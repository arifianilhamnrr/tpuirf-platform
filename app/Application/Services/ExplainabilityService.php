<?php

namespace App\Application\Services;

use App\Application\Interfaces\ExplainabilityServiceInterface;
use App\Domain\Repositories\RecommendationRepositoryInterface;
use Illuminate\Support\Facades\Http;

class ExplainabilityService implements ExplainabilityServiceInterface
{
    public function __construct(
        private readonly RecommendationRepositoryInterface $recommendationRepository,
    ) {}

    public function getExplanation(string $recommendationId): array
    {
        $recommendation = $this->recommendationRepository->findById($recommendationId);

        if (! $recommendation) {
            return [];
        }

        if ($recommendation->explanations) {
            return array_map(function (array $explanation): array {
                $feature = $explanation['feature'] ?? 'unknown';

                return $explanation + [
                    'human_readable' => $this->makeHumanReadable($feature, (float) ($explanation['shap_value'] ?? 0)),
                ];
            }, $recommendation->explanations);
        }

        $shapValues = $recommendation->shapValues;
        $features = $recommendation->features;

        if (! $shapValues || ! $features) {
            return [];
        }

        $explanations = [];

        $featureLabels = [
            'age' => 'Usia',
            'gender' => 'Jenis Kelamin',
            'education' => 'Tingkat Pendidikan',
            'occupation' => 'Pekerjaan',
            'digital_literacy' => 'Literasi Digital',
            'device_type' => 'Jenis Perangkat',
            'visual_preference' => 'Preferensi Visual',
            'context_environment' => 'Lingkungan Penggunaan',
            'ai_familiarity' => 'Pemahaman AI',
            'click_rate' => 'Tingkat Klik',
            'scroll_depth' => 'Kedalaman Gulir',
            'task_time' => 'Waktu Penyelesaian Tugas',
            'theme' => 'Tema',
            'color_palette' => 'Palet Warna',
            'font_family' => 'Keluarga Font',
            'font_size' => 'Ukuran Font',
            'layout_density' => 'Kepadatan Layout',
            'navigation_position' => 'Posisi Navigasi',
            'icon_style' => 'Gaya Ikon',
            'design_style' => 'Gaya Desain',
        ];

        foreach ($features as $index => $feature) {
            $shapValue = $shapValues[$index] ?? 0;
            $direction = $shapValue > 0 ? 'increases' : 'decreases';
            $impact = abs($shapValue);

            $explanations[] = [
                'feature' => $featureLabels[$feature] ?? $feature,
                'shap_value' => $shapValue,
                'impact' => $impact,
                'direction' => $direction,
                'human_readable' => $this->makeHumanReadable($feature, $shapValue),
            ];
        }

        usort($explanations, fn ($a, $b) => abs($b['shap_value']) - abs($a['shap_value']));

        return $explanations;
    }

    public function getHumanReadableExplanation(array $shapValues, array $features): array
    {
        $explanations = [];

        foreach ($features as $index => $feature) {
            $value = $shapValues[$index] ?? 0;
            $explanations[] = $this->makeHumanReadable($feature, $value);
        }

        return $explanations;
    }

    public function getFeatureImportance(): array
    {
        try {
            $response = Http::timeout(10)->get(config('app.ml_service_url', 'http://localhost:5000').'/feature_importance');

            return $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function makeHumanReadable(string $feature, float $shapValue): string
    {
        $impact = abs($shapValue);
        $strength = $impact > 0.1 ? 'secara kuat' : ($impact > 0.05 ? 'secara sedang' : 'secara ringan');

        $templates = [
            'age' => "Usia Anda $strength memengaruhi rekomendasi UI",
            'gender' => "Jenis kelamin Anda $strength memengaruhi gaya UI yang lebih sesuai",
            'education' => "Tingkat pendidikan Anda $strength memengaruhi preferensi kompleksitas antarmuka",
            'occupation' => "Pekerjaan Anda $strength memengaruhi pilihan tata letak UI",
            'digital_literacy' => "Tingkat literasi digital Anda $strength menentukan antarmuka yang direkomendasikan",
            'device_type' => "Perangkat yang Anda gunakan $strength memengaruhi rekomendasi UI",
            'visual_preference' => "Preferensi visual Anda $strength menentukan gaya desain yang direkomendasikan",
            'context_environment' => "Lingkungan penggunaan Anda $strength memengaruhi pilihan antarmuka",
            'ai_familiarity' => "Pemahaman Anda tentang AI $strength memengaruhi kompleksitas yang direkomendasikan",
            'click_rate' => "Perilaku klik Anda $strength memengaruhi rekomendasi UI",
            'scroll_depth' => "Pola gulir Anda $strength memengaruhi rekomendasi tata letak",
            'task_time' => "Kecepatan penyelesaian tugas Anda $strength menentukan kepadatan antarmuka yang direkomendasikan",
            'theme' => "Pilihan tema Anda $strength memengaruhi rekomendasi antarmuka",
            'color_palette' => "Palet warna yang dipilih $strength memengaruhi rekomendasi antarmuka",
            'font_family' => "Pilihan keluarga font Anda $strength memengaruhi rekomendasi antarmuka",
            'font_size' => "Ukuran font pilihan Anda $strength memengaruhi rekomendasi antarmuka",
            'layout_density' => "Kepadatan layout pilihan Anda $strength memengaruhi rekomendasi antarmuka",
            'navigation_position' => "Posisi navigasi pilihan Anda $strength memengaruhi rekomendasi antarmuka",
            'icon_style' => "Gaya ikon pilihan Anda $strength memengaruhi rekomendasi antarmuka",
            'design_style' => "Gaya desain pilihan Anda $strength memengaruhi rekomendasi antarmuka",
        ];

        return $templates[$feature] ?? "Fitur '$feature' $strength memengaruhi rekomendasi";
    }
}
