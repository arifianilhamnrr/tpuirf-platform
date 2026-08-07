@extends('layouts.app')

@section('title', 'Rekomendasi Anda')

@section('content')
    @php
        $preferenceLabels = [
            'light' => 'Mode Terang', 'dark' => 'Mode Gelap',
            'sans' => 'Inter', 'serif' => 'Merriweather', 'high_legibility' => 'Atkinson Hyperlegible',
            'small' => 'Kecil', 'medium' => 'Sedang', 'large' => 'Besar',
            'compact' => 'Rapat', 'comfortable' => 'Sedang', 'spacious' => 'Renggang',
            'top' => 'Navigasi Atas', 'left' => 'Bilah Samping Kiri', 'bottom' => 'Navigasi Bawah',
            'outline' => 'Outline', 'filled' => 'Filled', 'duotone' => 'Dual Tone',
            'minimal_flat' => 'Minimal / Datar', 'material_elevated' => 'Material / Berelevasi',
            'glassmorphism' => 'Glassmorphism', 'neumorphism' => 'Neumorphism',
            'high_contrast_brutalist' => 'Neobrutalism',
            'blue' => 'Biru', 'green' => 'Hijau', 'purple' => 'Ungu',
            'orange' => 'Oranye', 'rose' => 'Rose', 'neutral' => 'Netral',
        ];
        $preferenceValue = fn ($value) => $preferenceLabels[$value] ?? $value ?? '-';
    @endphp
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Hasil Preferensi Desain Anda</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Konfigurasi ini dirangkum dari pilihan dan perilaku interaksi Anda selama eksperimen.</p>
        </div>

        <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-8 border border-white/20 dark:border-gray-700/30 shadow-xl">
            @if($preferenceExperiment && !empty($finalPreferences))
                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                    <div class="w-24 h-24 shrink-0 rounded-3xl bg-gradient-to-br from-indigo-500 to-cyan-400 flex items-center justify-center shadow-2xl shadow-indigo-500/20">
                        <span class="text-3xl font-bold text-white">UI</span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Antarmuka hasil pilihan Anda</h2>
                        <p class="mt-1 text-gray-500 dark:text-gray-400">Gaya dan warna ini digunakan sebagai konfigurasi akhir desain.</p>
                        <div class="mt-3 flex flex-wrap gap-2 text-sm">
                            @foreach(($finalPreferences['design_styles'] ?? []) as $style)
                                <span class="rounded-full bg-indigo-500/10 px-3 py-1 font-medium text-indigo-700 dark:text-indigo-300">{{ $preferenceValue($style) }}</span>
                            @endforeach
                            @if(!empty($finalPreferences['color_palette']))
                                <span class="rounded-full bg-cyan-500/10 px-3 py-1 font-medium text-cyan-700 dark:text-cyan-300">Palet {{ $preferenceValue($finalPreferences['color_palette']) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-8 grid gap-3 sm:grid-cols-2">
                    @foreach([
                        'Tema' => $finalPreferences['theme'] ?? null,
                        'Palet Warna' => $finalPreferences['color_palette'] ?? null,
                        'Tipografi' => $finalPreferences['font_family'] ?? null,
                        'Ukuran Font' => $finalPreferences['font_size'] ?? null,
                        'Kepadatan Layout' => $finalPreferences['layout_density'] ?? null,
                        'Navigasi' => $finalPreferences['navigation_position'] ?? null,
                        'Gaya Ikon' => $finalPreferences['icon_style'] ?? null,
                    ] as $label => $value)
                        <div class="rounded-2xl bg-gray-50 dark:bg-gray-900/60 p-4">
                            <p class="text-xs uppercase tracking-wide text-gray-500">{{ $label }}</p>
                            <p class="mt-1 font-semibold text-gray-800 dark:text-white">{{ $preferenceValue($value) }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Belum ada hasil eksperimen</h2>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Selesaikan eksperimen preferensi untuk melihat konfigurasi desain akhir Anda.</p>
                </div>
            @endif
        </div>

        @if(!empty($explanations))
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-8 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6">Mengapa UI Ini?</h2>
                <div class="space-y-4">
                    @foreach($explanations as $explanation)
                        <div class="p-4 rounded-2xl bg-gradient-to-r from-indigo-500/5 to-cyan-500/5 border border-indigo-500/10">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-semibold text-gray-800 dark:text-white">{{ $explanation['feature'] }}</h3>
                                <span class="px-3 py-1 rounded-xl text-xs font-medium" 
                                    style="background: {{ $explanation['shap_value'] > 0 ? 'rgba(34,197,94,0.1)' : 'rgba(239,68,68,0.1)' }}; color: {{ $explanation['shap_value'] > 0 ? '#22C55E' : '#EF4444' }}">
                                    {{ $explanation['direction'] == 'increases' ? '+' : '-' }}{{ number_format($explanation['impact'], 3) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $explanation['human_readable'] }}</p>
                            <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" 
                                    style="width: {{ min(abs($explanation['shap_value']) * 100, 100) }}%; background: {{ $explanation['shap_value'] > 0 ? 'linear-gradient(to right, #22C55E, #16A34A)' : 'linear-gradient(to right, #EF4444, #DC2626)' }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(!$recommendation)
            <form method="POST" action="{{ route('respondent.recommendation.generate') }}" class="text-center">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 text-white font-semibold hover:from-indigo-700 hover:to-cyan-600 transition-all duration-200 shadow-lg shadow-indigo-500/25">
                    Buat Rekomendasi
                </button>
            </form>
        @endif
    </div>
@endsection
