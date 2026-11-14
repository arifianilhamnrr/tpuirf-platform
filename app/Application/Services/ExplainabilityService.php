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
        $featureKey = $feature;
        $val = $featureValue;
        $valStr = (string) ($featureValue ?? '');
        $recKey = (string) ($recommendedValue ?? '');
        $impactAbs = abs($shapValue);

        // Kasus netral / 0.000
        if ($impactAbs < 0.001) {
            $fieldLabel = match ($featureKey) {
                'age' => 'Untuk usia Anda' . ($val ? " ({$val} tahun)" : ''),
                'gender' => 'Data jenis kelamin' . ($val ? " (" . $this->valueLabel($val) . ")" : ''),
                'education' => 'Latar belakang pendidikan' . ($val ? " (" . $this->valueLabel($val) . ")" : ''),
                'occupation' => 'Data pekerjaan' . ($val ? " ({$val})" : ''),
                default => 'Faktor ' . mb_strtolower($this->featureLabel($featureKey)),
            };
            $targetDesc = $this->targetLabel($target) . ' ' . $this->valueLabel($recommendedValue);
            return "{$fieldLabel} bersifat netral dan tidak memengaruhi penentuan {$targetDesc}.";
        }

        // Positif (Mendukung)
        if ($shapValue > 0) {
            return match ($featureKey) {
                'age' => match ($target) {
                    'icon_style' => "Di rentang usia Anda ({$val} tahun), gaya ikon {$this->valueLabel($recommendedValue)} terbukti paling disukai karena memberikan kesan bersih, modern, dan tidak membuat layar terasa penuh.",
                    'font_size' => "Faktor usia Anda ({$val} tahun) selaras dengan ukuran teks {$this->valueLabel($recommendedValue)} agar keterbacaan tetap optimal dan nyaman di mata.",
                    'navigation_position' => "Karakteristik usia Anda ({$val} tahun) sangat cocok dengan tata letak {$this->valueLabel($recommendedValue)} yang ergonomis.",
                    default => "Rentang usia Anda ({$val} tahun) memberikan kesesuaian tinggi terhadap pilihan {$this->targetLabel($target)} {$this->valueLabel($recommendedValue)}.",
                },
                'digital_literacy' => match ($target) {
                    'font_family' => "Dengan tingkat literasi digital Anda yang tinggi (skor {$val}), jenis huruf {$this->valueLabel($recommendedValue)} sangat direkomendasikan karena dirancang khusus untuk keterbacaan tajam pada layar digital dan antarmuka modern.",
                    'layout_density' => "Tingkat literasi digital Anda (skor {$val}) memudahkan Anda memindai banyak informasi sekaligus, sehingga tata letak {$this->valueLabel($recommendedValue)} dipilih agar lebih efisien tanpa banyak scrolling.",
                    default => "Tingkat pemahaman teknologi Anda (skor {$val}) mendukung pemilihan {$this->targetLabel($target)} {$this->valueLabel($recommendedValue)} untuk navigasi yang lebih leluasa.",
                },
                'visual_preference' => match ($target) {
                    'icon_style' => "Pilihan gaya visual {$this->valueLabel($val)} Anda sangat selaras dengan ikon {$this->valueLabel($recommendedValue)} yang minimalis dan rapi.",
                    'font_size' => "Untuk mendukung gaya visual {$this->valueLabel($val)} pilihan Anda, ukuran huruf {$this->valueLabel($recommendedValue)} memberikan keseimbangan terbaik antara estetika ruang kosong dan kemudahan membaca.",
                    'layout_density' => "Gaya visual {$this->valueLabel($val)} yang Anda sukai sangat cocok dipadukan dengan tata letak {$this->valueLabel($recommendedValue)} yang presisi dan terorganisir.",
                    default => "Preferensi visual {$this->valueLabel($val)} Anda berpadu serasi dengan {$this->targetLabel($target)} {$this->valueLabel($recommendedValue)}.",
                },
                'primary_device_purpose' => match ($target) {
                    'navigation_position' => "Karena Anda sering menggunakan perangkat untuk kebutuhan {$this->valueLabel($val)}, menu di {$this->valueLabel($recommendedValue)} sangat membantu Anda berpindah antar-konten atau fitur dengan lebih terstruktur.",
                    default => "Aktivitas utama Anda untuk {$this->valueLabel($val)} menjadikan {$this->targetLabel($target)} {$this->valueLabel($recommendedValue)} pilihan paling fungsional.",
                },
                'device_usage_time' => match ($target) {
                    'font_family' => "Untuk kebiasaan penggunaan di waktu {$this->valueLabel($val)}, huruf {$this->valueLabel($recommendedValue)} memiliki proporsi spasi yang nyaman sehingga mata tidak cepat lelah saat kondisi cahaya berubah.",
                    'theme' => "Kebiasaan berinteraksi di waktu {$this->valueLabel($val)} sangat cocok dipadukan dengan {$this->valueLabel($recommendedValue)} untuk kenyamanan visual.",
                    default => "Waktu penggunaan utama di {$this->valueLabel($val)} mendukung kenyamanan {$this->targetLabel($target)} {$this->valueLabel($recommendedValue)}.",
                },
                'education' => match ($target) {
                    'navigation_position' => "Latar belakang pendidikan Anda (" . $this->valueLabel($val) . ") menunjukkan kecenderungan alur navigasi yang lebih nyaman dan familiar dengan tata letak {$this->valueLabel($recommendedValue)}.",
                    default => "Profil pendidikan Anda (" . $this->valueLabel($val) . ") selaras dengan kepraktisan {$this->targetLabel($target)} {$this->valueLabel($recommendedValue)}.",
                },
                'occupation' => "Sebagai {$val}, pola interaksi Anda menunjukkan kecocokan tinggi dengan karakteristik {$this->targetLabel($target)} {$this->valueLabel($recommendedValue)}.",
                'avg_task_time', 'total_session_time' => "Efisiensi waktu interaksi Anda (" . $this->formatTime($val) . ") memberikan indikasi kuat bahwa {$this->targetLabel($target)} {$this->valueLabel($recommendedValue)} membuat alur kerja Anda lebih lancar.",
                default => "Karakteristik " . mb_strtolower($this->featureLabel($featureKey)) . " Anda (" . $this->valueLabel($val) . ") selaras dengan pemilihan {$this->targetLabel($target)} {$this->valueLabel($recommendedValue)}.",
            };
        }

        // Negatif (Sedikit mengurangi / catatan)
        $timeStr = in_array($featureKey, ['avg_task_time', 'total_session_time']) ? $this->formatTime($val) : $this->valueLabel($val);

        return match ($featureKey) {
            'avg_task_time', 'total_session_time' => match ($target) {
                'font_size' => "Kecepatan waktu penyelesaian tugas Anda ({$timeStr}) sebenarnya cenderung lebih cocok untuk ukuran teks yang sedikit berbeda, namun faktor kenyamanan lain membuat ukuran {$this->valueLabel($recommendedValue)} tetap menjadi pilihan paling seimbang.",
                'font_family' => "Pola kecepatan interaksi Anda ({$timeStr}) sedikit mengurangi kecocokan pada huruf {$this->valueLabel($recommendedValue)}, tetapi keunggulan keterbacaannya di faktor lain tetap menjadikannya pilihan utama.",
                'layout_density' => "Waktu penyelesaian tugas Anda ({$timeStr}) memberi sedikit catatan agar tampilan tidak terlalu padat, namun dorongan dari tingkat literasi digital Anda membuat layout {$this->valueLabel($recommendedValue)} tetap dipertahankan.",
                'icon_style' => "Kecepatan interaksi Anda ({$timeStr}) memberi pengaruh yang sangat kecil terhadap ikon {$this->valueLabel($recommendedValue)}, sehingga tidak mengubah rekomendasi utama.",
                default => "Durasi interaksi Anda ({$timeStr}) memberi sedikit koreksi pada {$this->targetLabel($target)}, namun tetap paling stabil di konfigurasi {$this->valueLabel($recommendedValue)}.",
            },
            'occupation' => "Sebagai {$val}, Anda sebenarnya kerap berhadapan dengan kebutuhan visual yang variatif, namun opsi {$this->targetLabel($target)} {$this->valueLabel($recommendedValue)} tetap menjadi titik temu paling nyaman.",
            'age' => "Faktor usia Anda ({$val} tahun) sedikit mengarah pada preferensi alternatif, tetapi kebutuhan aktivitas utama Anda membuat {$this->targetLabel($target)} {$this->valueLabel($recommendedValue)} jauh lebih optimal.",
            'digital_literacy' => "Tingkat literasi digital Anda (skor {$val}) memberi sedikit penyesuaian, namun pertimbangan ergonomis tetap mengutamakan {$this->targetLabel($target)} {$this->valueLabel($recommendedValue)}.",
            default => "Karakteristik " . mb_strtolower($this->featureLabel($featureKey)) . " Anda ({$timeStr}) memberi sedikit catatan pada {$this->targetLabel($target)}, namun kombinasi faktor kenyamanan lainnya memberi dukungan lebih dominan.",
        };
    }

    private function formatTime(mixed $ms): string
    {
        if (!is_numeric($ms) || (float) $ms <= 0) {
            return 'interaksi standar';
        }

        $num = (float) $ms;
        if ($num > 1000) {
            $seconds = round($num / 1000, 1);
            return "{$seconds} detik";
        }

        return "{$num} ms";
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
            'top' => 'Navigasi Atas', 'left' => 'Bilah Samping Kiri', 'bottom' => 'Navigasi Bawah',
            'small' => 'Kecil', 'medium' => 'Sedang', 'large' => 'Besar',
            'compact' => 'Rapat', 'comfortable' => 'Sedang', 'spacious' => 'Renggang',
            'outline' => 'Outline', 'filled' => 'Filled', 'duotone' => 'Dual Tone',
            'high_school' => 'High School', 'bachelor' => 'S1 / Sarjana', 'master' => 'S2 / Magister',
        ][(string) $value] ?? str((string) $value)->replace('_', ' ')->toString();
    }
}
