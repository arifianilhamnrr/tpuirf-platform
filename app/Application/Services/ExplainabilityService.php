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
            return array_map(
                fn (array $explanation): array => $this->normalizeExplanation(
                    $explanation,
                    $recommendation->inputFeatures ?? [],
                ),
                $recommendation->explanations,
            );
        }

        $shapValues = $recommendation->shapValues;
        $features = $recommendation->features;

        if (! $shapValues || ! $features) {
            return [];
        }

        $explanations = [];

        foreach ($features as $index => $feature) {
            $shapValue = $shapValues[$index] ?? 0;
            $explanations[] = $this->normalizeExplanation([
                'feature' => $feature,
                'shap_value' => $shapValue,
                'impact' => abs($shapValue),
                'direction' => $shapValue >= 0 ? 'increases' : 'decreases',
            ], $recommendation->inputFeatures ?? []);
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

    private function normalizeExplanation(array $explanation, array $input): array
    {
        $feature = $explanation['feature'] ?? 'unknown';
        $target = $explanation['target'] ?? 'interface';
        $recommendedValue = $explanation['recommended_value'] ?? null;
        $featureValue = $explanation['feature_value'] ?? ($input[$feature] ?? null);
        $shapValue = (float) ($explanation['shap_value'] ?? 0);

        return array_merge($explanation, [
            'feature_key' => $feature,
            'feature' => $this->featureLabel($feature),
            'feature_value' => $featureValue,
            'target' => $target,
            'target_label' => $this->targetLabel($target),
            'recommended_value_label' => $this->valueLabel($recommendedValue),
            'impact' => (float) ($explanation['impact'] ?? abs($shapValue)),
            'direction' => $explanation['direction'] ?? ($shapValue >= 0 ? 'increases' : 'decreases'),
            'human_readable' => $explanation['human_readable']
                ?? $this->makeHumanReadable($feature, $shapValue, $target, $recommendedValue, $featureValue),
        ]);
    }

    private function makeHumanReadable(
        string $feature,
        float $shapValue,
        string $target = 'interface',
        mixed $recommendedValue = null,
        mixed $featureValue = null,
    ): string {
        $featureLabel = mb_strtolower($this->featureLabel($feature));
        $targetLabel = mb_strtolower($this->targetLabel($target));
        $valueLabel = $this->valueLabel($featureValue);
        $recommendationLabel = $this->valueLabel($recommendedValue);

        if ($shapValue >= 0) {
            return "Data {$featureLabel} Anda ({$valueLabel}) mendukung rekomendasi {$targetLabel} {$recommendationLabel} menurut pola yang dipelajari model.";
        }

        return "Data {$featureLabel} Anda ({$valueLabel}) mengurangi keyakinan pada rekomendasi {$targetLabel} {$recommendationLabel}; kombinasi faktor lain memberi dukungan lebih kuat.";
    }

    private function featureLabel(string $feature): string
    {
        return [
            'age' => 'Usia', 'gender' => 'Jenis Kelamin', 'education' => 'Tingkat Pendidikan',
            'occupation' => 'Pekerjaan', 'digital_literacy' => 'Literasi Digital',
            'device_type' => 'Jenis Perangkat', 'visual_preference' => 'Preferensi Visual',
            'context_environment' => 'Lingkungan Penggunaan', 'ai_familiarity' => 'Pemahaman AI',
            'daily_device_usage' => 'Durasi Penggunaan Perangkat', 'device_usage_time' => 'Waktu Penggunaan Utama',
            'primary_device_purpose' => 'Tujuan Penggunaan', 'click_count' => 'Jumlah Klik',
            'scroll_count' => 'Jumlah Gulir', 'hover_count' => 'Jumlah Hover',
            'keyboard_count' => 'Interaksi Keyboard', 'avg_task_time' => 'Rata-rata Waktu Tugas',
            'total_session_time' => 'Total Waktu Aktif',
        ][$feature] ?? str($feature)->replace('_', ' ')->title()->toString();
    }

    private function targetLabel(string $target): string
    {
        return [
            'theme' => 'Tema', 'color_palette' => 'Palet Warna', 'font_family' => 'Keluarga Font',
            'font_size' => 'Ukuran Font', 'layout_density' => 'Kepadatan Layout',
            'navigation_position' => 'Posisi Navigasi', 'icon_style' => 'Gaya Ikon',
            'design_style' => 'Gaya Desain', 'interface' => 'Antarmuka',
        ][$target] ?? str($target)->replace('_', ' ')->title()->toString();
    }

    private function valueLabel(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'tidak tersedia';
        }

        return [
            'morning' => 'pagi', 'afternoon' => 'siang', 'evening' => 'sore', 'night' => 'malam',
            'under_1' => '< 1 jam/hari', '1_3' => '1–3 jam/hari', '3_5' => '3–5 jam/hari',
            '5_8' => '5–8 jam/hari', 'over_8' => '> 8 jam/hari',
            'light' => 'mode terang', 'dark' => 'mode gelap', 'sans' => 'Inter',
            'serif' => 'Merriweather', 'high_legibility' => 'Atkinson Hyperlegible',
            'minimal_flat' => 'Minimal / Datar', 'material_elevated' => 'Material / Berelevasi',
            'high_contrast_brutalist' => 'Neobrutalism',
        ][(string) $value] ?? str((string) $value)->replace('_', ' ')->toString();
    }
}
