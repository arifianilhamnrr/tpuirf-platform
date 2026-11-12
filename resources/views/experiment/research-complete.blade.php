@extends('layouts.app')

@section('title', 'Eksperimen Selesai')

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
        'high_contrast_brutalist' => 'Kontras Tinggi / Brutalis',
    ];
@endphp
<div class="max-w-3xl mx-auto text-center">
    <div class="rounded-3xl border border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-800/70 p-8 sm:p-12 shadow-xl">
        <div class="mx-auto w-20 h-20 rounded-full bg-emerald-500 text-white flex items-center justify-center text-4xl">✓</div>
        <h1 class="mt-6 text-3xl font-bold text-gray-900 dark:text-white">Eksperimen Selesai</h1>
        <p class="mt-3 text-gray-600 dark:text-gray-300">Terima kasih. Seluruh pilihan dan data interaksi Anda telah disimpan.</p>

        <div class="mt-8 grid sm:grid-cols-2 gap-3 text-left">
            @foreach([
                 'Tema' => $experiment['preferences']['theme'] ?? '-',
                 'Palet Warna' => $experiment['preferences']['color_palette'] ?? '-',
                'Font' => trim(($experiment['preferences']['font_family'] ?? '-').' · '.($experiment['preferences']['font_size'] ?? '-')),
                'Tata Letak' => $experiment['preferences']['layout_density'] ?? '-',
                'Navigasi' => $experiment['preferences']['navigation_position'] ?? '-',
                'Ikon' => $experiment['preferences']['icon_style'] ?? '-',
                'Gaya Desain' => isset($experiment['preferences']['design_styles'])
                    ? implode(' · ', $experiment['preferences']['design_styles'])
                    : '-',
            ] as $label => $value)
                <div class="rounded-2xl bg-gray-50 dark:bg-gray-900/60 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ $label }}</p>
                    <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ collect(explode(' · ', $value))->map(fn ($item) => $preferenceLabels[$item] ?? $item)->join(' · ') }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-8 text-sm text-gray-500 dark:text-gray-400">Durasi aktif tercatat: {{ number_format(($experiment['total_active_duration_ms'] ?? 0) / 1000, 1) }} detik</div>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('respondent.recommendation.show') }}" class="inline-flex px-8 py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 text-white font-semibold shadow-lg shadow-indigo-500/25 hover:from-indigo-700 hover:to-cyan-600 transition-all">Lihat Analisis AI & SHAP</a>
            <a href="{{ route('respondent.dashboard') }}" class="inline-flex px-8 py-3.5 rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">Kembali ke Dashboard</a>
        </div>
    </div>
</div>
@endsection
