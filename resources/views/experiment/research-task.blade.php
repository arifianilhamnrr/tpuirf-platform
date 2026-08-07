@extends('layouts.app')

@section('title', $taskDefinition['label'])

@php
    $labels = [
        'light' => 'Mode Terang', 'dark' => 'Mode Gelap',
        'sans' => 'Inter', 'serif' => 'Merriweather', 'high_legibility' => 'Atkinson Hyperlegible',
        'small' => 'Kecil', 'medium' => 'Sedang', 'large' => 'Besar',
        'compact' => 'Rapat', 'comfortable' => 'Sedang', 'spacious' => 'Renggang',
        'top' => 'Navigasi Atas', 'left' => 'Bilah Samping Kiri', 'bottom' => 'Navigasi Bawah',
        'outline' => 'Garis', 'filled' => 'Penuh', 'duotone' => 'Dua Warna',
         'minimal_flat' => 'Minimal / Datar', 'material_elevated' => 'Material / Berelevasi',
         'glassmorphism' => 'Glassmorphism', 'neumorphism' => 'Neumorphism',
         'high_contrast_brutalist' => 'Kontras Tinggi / Brutalis',
        'blue' => 'Biru', 'green' => 'Hijau', 'purple' => 'Ungu', 'orange' => 'Oranye', 'rose' => 'Rose', 'neutral' => 'Netral',
    ];
    $descriptions = [
        'minimal_flat' => 'Warna solid, bersih, dan minim dekorasi.',
        'material_elevated' => 'Hierarki jelas dengan permukaan dan efek elevasi.',
        'glassmorphism' => 'Permukaan transparan, efek kabur, dan garis tepi tipis.',
        'neumorphism' => 'Permukaan lembut dengan bayangan dalam dan luar.',
        'high_contrast_brutalist' => 'Kontras tinggi, border tegas, dan bentuk eksplisit.',
    ];
    $previousTaskKey = $experiment['task_order'][$task['sequence'] - 2] ?? null;
@endphp

@section('content')
<div
    class="max-w-6xl mx-auto"
    x-data="researchPreferenceTask(@js([
        'experimentId' => $experiment['id'],
        'taskKey' => $task['task_key'],
        'sequence' => $task['sequence'],
        'isReviewing' => $task['status'] === 'completed',
        'initialSelectedOption' => $task['selected_option'] ?? $task['draft_option'],
        'initialSelectionPayload' => $task['selection_payload'] ?? $task['draft_selection_payload'] ?? [],
        'combinedPreferences' => $experiment['preferences'] ?? [],
        'optionLabels' => $labels,
        'initialSequence' => $experiment['last_event_sequence'],
        'taskOrder' => $experiment['task_order'],
        'experimentStartedAt' => $experiment['started_at'],
        'idleThresholdMs' => $idleThresholdSeconds * 1000,
        'eventsUrl' => route('respondent.preference-experiment.events.batch', $experiment['id']),
        'selectionUrl' => route('respondent.preference-experiment.tasks.select', [$experiment['id'], $task['task_key']]),
        'taskPageUrl' => route('respondent.preference-experiment.tasks.page', [$experiment['id'], '__TASK__']),
        'previousTaskUrl' => $previousTaskKey ? route('respondent.preference-experiment.tasks.page', [$experiment['id'], $previousTaskKey]) : null,
        'evaluationUrl' => route('respondent.preference-experiment.evaluation.page', $experiment['id']),
    ]))"
>
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">Tugas {{ $task['sequence'] }} dari {{ count($experiment['task_order']) }}</p>
                <h1 class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $taskDefinition['label'] }}</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-300">{{ $task['task_key'] === 'design_style' ? 'Preferensi Anda dari tugas sebelumnya sudah diterapkan. Coba setiap gaya dan pilih satu atau lebih yang Anda sukai.' : 'Coba setiap pilihan, lalu pilih yang paling nyaman menurut Anda.' }}</p>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Aktif <span class="font-semibold" x-text="formatDuration(activeMs)"></span>
                · Diam <span class="font-semibold" x-text="formatDuration(idleMs)"></span>
            </div>
        </div>
        <div class="mt-4 h-2 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-cyan-400" style="width: {{ ($task['sequence'] / count($experiment['task_order'])) * 100 }}%"></div>
        </div>
    </div>

    @if($task['task_key'] === 'theme')
        <div class="grid md:grid-cols-2 gap-5">
            @foreach($task['option_order'] as $option)
                <button type="button" @pointerdown="rememberThemePointer($event)" @click="choose('{{ $option }}', $event)" data-track-component="theme-{{ $option }}-card" data-option-id="{{ $option }}"
                    class="text-left rounded-3xl border-2 p-2 transition"
                    :class="selectedOption === '{{ $option }}' ? 'border-indigo-500 ring-4 ring-indigo-500/15' : 'border-transparent'">
                    <div class="rounded-2xl p-6 min-h-72 {{ $option === 'dark' ? 'bg-slate-900 text-white' : 'bg-white text-slate-900 border border-slate-200' }}">
                        <div class="flex justify-between items-center"><strong>Ruang Kerja</strong><span class="text-sm opacity-60">{{ $labels[$option] }}</span></div>
                        <div class="mt-8 grid grid-cols-2 gap-3">
                            <div class="rounded-xl p-4 {{ $option === 'dark' ? 'bg-slate-800' : 'bg-slate-100' }}"><p class="text-sm opacity-60">Proyek</p><p class="text-2xl font-bold mt-2">24</p></div>
                            <div class="rounded-xl p-4 {{ $option === 'dark' ? 'bg-slate-800' : 'bg-slate-100' }}"><p class="text-sm opacity-60">Selesai</p><p class="text-2xl font-bold mt-2">81%</p></div>
                        </div>
                        <div class="mt-5 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center">Tindakan utama</div>
                    </div>
                </button>
            @endforeach
        </div>
    @elseif($task['task_key'] === 'typography')
        <div class="grid lg:grid-cols-[320px_1fr] gap-6">
            <div class="space-y-6 rounded-3xl bg-white/75 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 p-6">
                <div>
                    <h2 class="font-semibold text-gray-900 dark:text-white">Keluarga Font</h2>
                    <div class="mt-3 space-y-2">
                        @foreach($task['option_order']['font_family'] as $option)
                            <label data-track-component="font-family-{{ $option }}" data-option-id="{{ $option }}"
                                class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer text-gray-800 dark:text-gray-100 transition"
                                :class="fontFamily === '{{ $option }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/15' : 'border-gray-200 dark:border-gray-700'">
                                <input type="radio" class="accent-indigo-600" x-model="fontFamily" value="{{ $option }}" @change="selectionChanged('{{ $option }}')">
                                <span>{{ $labels[$option] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <h2 class="font-semibold text-gray-900 dark:text-white">Ukuran font</h2>
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        @foreach($task['option_order']['font_size'] as $option)
                            <label data-track-component="font-size-{{ $option }}" data-option-id="{{ $option }}"
                                class="text-center p-3 rounded-xl border cursor-pointer text-gray-800 dark:text-gray-100 transition"
                                :class="fontSize === '{{ $option }}' ? 'border-indigo-500 bg-indigo-600 text-white dark:text-white' : 'border-gray-200 dark:border-gray-700'">
                                <input type="radio" class="sr-only" x-model="fontSize" value="{{ $option }}" @change="selectionChanged('{{ $option }}')">
                                <span>{{ $labels[$option] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-gray-700 p-8 text-gray-900 dark:text-white"
                :class="[fontFamilyClass(), fontSizeClass()]" data-track-component="typography-preview">
                <p class="text-sm text-indigo-600 dark:text-indigo-400">Pratinjau tipografi langsung</p>
                <h2 class="mt-3 text-3xl font-bold">Antarmuka yang jelas dimulai dari tipografi yang mudah dibaca.</h2>
                <p class="mt-5 leading-relaxed opacity-75">Contoh paragraf ini memakai konten dan lebar yang sama. Cobalah membaca beberapa baris untuk menentukan kombinasi font dan ukuran yang paling nyaman.</p>
                <button type="button" class="mt-6 px-5 py-3 rounded-xl bg-indigo-600 text-white" data-track-component="typography-preview-action">Lanjutkan membaca</button>
            </div>
        </div>
    @elseif($task['task_key'] === 'layout_density')
        <div class="grid lg:grid-cols-3 gap-5">
            @foreach($task['option_order'] as $option)
                @php
                    $gap = $option === 'compact' ? 'gap-2 p-3' : ($option === 'spacious' ? 'gap-6 p-8' : 'gap-4 p-5');
                    $padding = $option === 'compact' ? 'p-3' : ($option === 'spacious' ? 'p-6' : 'p-4');
                @endphp
                <button type="button" @click="choose('{{ $option }}')" data-track-component="layout-{{ $option }}-preview" data-option-id="{{ $option }}"
                    class="text-left rounded-3xl border-2 bg-white dark:bg-gray-800 transition {{ $gap }}"
                    :class="selectedOption === '{{ $option }}' ? 'border-indigo-500 ring-4 ring-indigo-500/15' : 'border-gray-200 dark:border-gray-700'">
                    <strong class="text-gray-900 dark:text-white">{{ $labels[$option] }}</strong>
                    <div class="grid {{ $option === 'spacious' ? 'grid-cols-1' : 'grid-cols-2' }} {{ $gap }}">
                        @foreach(range(1, 4) as $card)
                            <div class="rounded-xl bg-slate-100 dark:bg-slate-700 {{ $padding }}"><div class="h-3 w-2/3 rounded bg-slate-300 dark:bg-slate-500"></div><div class="mt-3 h-8 rounded bg-indigo-200 dark:bg-indigo-800"></div></div>
                        @endforeach
                    </div>
                </button>
            @endforeach
        </div>
    @elseif($task['task_key'] === 'navigation_position')
        <div class="grid lg:grid-cols-3 gap-5">
            @foreach($task['option_order'] as $option)
                <button type="button" @click="choose('{{ $option }}')" data-track-component="navigation-{{ $option }}-preview" data-option-id="{{ $option }}"
                    class="text-left rounded-3xl border-2 p-4 bg-white dark:bg-gray-800 transition"
                    :class="selectedOption === '{{ $option }}' ? 'border-indigo-500 ring-4 ring-indigo-500/15' : 'border-gray-200 dark:border-gray-700'">
                    <strong class="text-gray-900 dark:text-white">{{ $labels[$option] }}</strong>
                    <div class="mt-4 h-64 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-900 flex {{ $option === 'top' || $option === 'bottom' ? 'flex-col' : '' }}">
                        @if($option === 'top')
                            <div class="h-12 bg-indigo-600 text-white flex items-center justify-around text-xs"><span>Beranda</span><span>Jelajah</span><span>Aktivitas</span><span>Profil</span></div>
                        @elseif($option === 'left')
                            <div class="w-20 bg-indigo-600 text-white flex flex-col justify-center gap-6 items-center text-xs"><span>Beranda</span><span>Jelajah</span><span>Aktivitas</span><span>Profil</span></div>
                        @endif
                        <div class="flex-1 p-4"><div class="h-20 bg-white dark:bg-slate-800 rounded-xl"></div><div class="mt-3 grid grid-cols-2 gap-3"><div class="h-20 bg-white dark:bg-slate-800 rounded-xl"></div><div class="h-20 bg-white dark:bg-slate-800 rounded-xl"></div></div></div>
                        @if($option === 'bottom')
                            <div class="h-12 bg-indigo-600 text-white flex items-center justify-around text-xs"><span>Beranda</span><span>Jelajah</span><span>Aktivitas</span><span>Profil</span></div>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>
    @elseif($task['task_key'] === 'icon_style')
        <div class="grid md:grid-cols-3 gap-5">
            @foreach($task['option_order'] as $option)
                <button type="button" @click="choose('{{ $option }}')" data-track-component="icon-{{ $option }}-preview" data-option-id="{{ $option }}"
                    class="rounded-3xl border-2 p-7 bg-white dark:bg-gray-800 transition"
                    :class="selectedOption === '{{ $option }}' ? 'border-indigo-500 ring-4 ring-indigo-500/15' : 'border-gray-200 dark:border-gray-700'">
                    <strong class="text-gray-900 dark:text-white">{{ $labels[$option] }}</strong>
                    <div class="mt-8 flex justify-center gap-6 text-indigo-600 dark:text-indigo-400" aria-label="Contoh ikon {{ $labels[$option] }}">
                        @foreach(['fa-house' => 'Beranda', 'fa-folder' => 'Konten', 'fa-circle-check' => 'Konfirmasi'] as $icon => $iconLabel)
                            @if($option === 'duotone')
                                <span class="relative inline-flex w-10 h-10 items-center justify-center" title="{{ $iconLabel }}">
                                    <i class="fa-solid {{ $icon }} absolute text-[40px] opacity-30 text-cyan-500" aria-hidden="true"></i>
                                    <i class="fa-solid {{ $icon }} absolute text-[34px] text-indigo-700 dark:text-indigo-300 translate-x-0.5 -translate-y-0.5" aria-hidden="true"></i>
                                </span>
                            @elseif($option === 'filled')
                                <i class="fa-solid {{ $icon }} text-[40px] w-10 text-center" title="{{ $iconLabel }}" aria-hidden="true"></i>
                            @else
                                <i class="fa-solid {{ $icon }} icon-outline-preview text-[40px] w-10 text-center" title="{{ $iconLabel }}" aria-hidden="true"></i>
                            @endif
                        @endforeach
                    </div>
                    <p class="mt-6 text-sm text-gray-500">Beranda · Konten · Konfirmasi</p>
                </button>
            @endforeach
        </div>
    @elseif($task['task_key'] === 'design_style')
        @php
            $paletteDetails = [
                'blue' => ['label' => 'Biru', 'classes' => 'from-sky-500 to-indigo-600', 'swatches' => ['#38bdf8', '#6366f1', '#172554']],
                'green' => ['label' => 'Hijau', 'classes' => 'from-emerald-400 to-teal-700', 'swatches' => ['#34d399', '#14b8a6', '#134e4a']],
                'purple' => ['label' => 'Ungu', 'classes' => 'from-violet-500 to-fuchsia-700', 'swatches' => ['#a78bfa', '#d946ef', '#581c87']],
                'orange' => ['label' => 'Oranye', 'classes' => 'from-amber-400 to-orange-600', 'swatches' => ['#fbbf24', '#f97316', '#7c2d12']],
                'rose' => ['label' => 'Rose', 'classes' => 'from-pink-400 to-rose-700', 'swatches' => ['#f472b6', '#f43f5e', '#881337']],
                'neutral' => ['label' => 'Netral', 'classes' => 'from-slate-300 to-slate-700', 'swatches' => ['#e2e8f0', '#64748b', '#1e293b']],
            ];
        @endphp
        <div class="mb-6 rounded-3xl border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50/80 dark:bg-indigo-500/10 p-5">
            <p class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">Konfigurasi pilihan Anda yang diterapkan</p>
            <div class="mt-3 flex flex-wrap gap-2 text-xs font-medium text-gray-700 dark:text-gray-200">
                <template x-for="item in combinedPreferenceLabels()" :key="item">
                    <span class="rounded-full border border-indigo-200 dark:border-indigo-500/30 bg-white/80 dark:bg-gray-900/50 px-3 py-1.5" x-text="item"></span>
                </template>
            </div>
        </div>
        <div class="space-y-4">
            @foreach($task['option_order'] as $option)
                @php
                    $surface = match($option) {
                        'glassmorphism' => 'bg-white/30 backdrop-blur-xl border border-white/60 shadow-xl',
                        'neumorphism' => 'bg-slate-200 border-0 shadow-[10px_10px_24px_#b8c0cc,-10px_-10px_24px_#ffffff]',
                        'material_elevated' => 'bg-white border-0 shadow-xl',
                         'high_contrast_brutalist' => 'tpuirf-palette-accent border-4 border-black shadow-[7px_7px_0_#000]',
                        default => 'bg-white border-2 border-slate-200 shadow-none',
                    };
                    $buttonStyle = $option === 'high_contrast_brutalist' ? 'bg-black text-white border-2 border-black rounded-none' : ($option === 'neumorphism' ? 'bg-slate-200 tpuirf-palette-primary-text shadow-[inset_3px_3px_6px_#b8c0cc,inset_-3px_-3px_6px_#ffffff]' : 'tpuirf-palette-primary text-white');
                @endphp
                <section class="rounded-3xl border-2 overflow-hidden bg-white/70 dark:bg-gray-800/60 transition" :class="isStyleSelected('{{ $option }}') ? 'border-emerald-500 ring-4 ring-emerald-500/10' : 'border-gray-200 dark:border-gray-700'">
                    <button type="button" class="w-full flex items-center justify-between gap-4 p-5 text-left" @click="togglePreview('{{ $option }}')" data-track-component="design-{{ $option }}-accordion" data-option-id="{{ $option }}" :aria-expanded="expandedStyle === '{{ $option }}'">
                        <div><h2 class="font-bold text-gray-900 dark:text-white">{{ $labels[$option] }}</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $descriptions[$option] }}</p></div>
                        <span class="text-2xl" x-text="expandedStyle === '{{ $option }}' ? '−' : '+'"></span>
                    </button>
                    <div x-show="expandedStyle === '{{ $option }}'" x-cloak class="p-4 sm:p-7" :style="paletteVariables()" :class="previewBackgroundClass('{{ $option }}')">
                        <div class="overflow-hidden" :style="paletteVariables()" :class="[compactSurfaceClass('{{ $option }}'), combinedFontClass(), combinedFontSizeClass()]" data-track-component="design-{{ $option }}-live-preview" data-option-id="{{ $option }}">
                            <div x-show="combinedPreferences.navigation_position === 'top'" class="px-5 py-3 flex items-center justify-between" :class="compactNavigationClass('{{ $option }}')"><strong>Ruang Kerja</strong><span class="text-xs">Beranda · Proyek · Aktivitas</span></div>
                            <div class="flex" :class="combinedPreferences.navigation_position === 'left' ? '' : 'flex-col'">
                                <div x-show="combinedPreferences.navigation_position === 'left'" class="w-24 shrink-0 p-3 space-y-5 text-center text-xs" :class="compactNavigationClass('{{ $option }}')"><strong>Ruang Kerja</strong><p>Beranda</p><p>Proyek</p><p>Aktivitas</p></div>
                                <div class="flex-1" :class="combinedDensityClass()">
                                    <div class="flex justify-between items-center"><strong>Ruang kerja kreatif</strong><span class="text-xs opacity-60">Pratinjau gabungan</span></div>
                                    <div class="grid sm:grid-cols-2" :class="combinedGridGapClass()">
                                        <div class="p-4" :class="compactSurfaceClass('{{ $option }}', true)"><p class="text-sm opacity-60">Proyek aktif</p><p class="text-3xl font-bold mt-2">12</p></div>
                                        <div class="p-4" :class="compactSurfaceClass('{{ $option }}', true)"><p class="text-sm opacity-60">Progres tim</p><p class="text-3xl font-bold mt-2">76%</p></div>
                                    </div>
                                    <div class="mt-5 flex flex-wrap gap-3">
                                        <button type="button" class="px-5 py-3" :class="compactButtonClass('{{ $option }}', true)" data-track-component="design-{{ $option }}-primary-action" data-option-id="{{ $option }}">Tindakan utama</button>
                                        <button type="button" class="px-5 py-3" :class="compactButtonClass('{{ $option }}', false)" @click.stop="openFullscreen('{{ $option }}')" data-track-component="design-{{ $option }}-fullscreen" data-option-id="{{ $option }}">Coba Layar Penuh</button>
                                        <button type="button" class="px-5 py-3" :class="compactSelectionButtonClass('{{ $option }}')" @click.stop="toggleStyleSelection('{{ $option }}')" data-track-component="design-{{ $option }}-select" data-option-id="{{ $option }}" x-text="isStyleSelected('{{ $option }}') ? 'Batalkan Pilihan' : 'Pilih Gaya Ini'"></button>
                                    </div>
                                </div>
                            </div>
                            <div x-show="combinedPreferences.navigation_position === 'bottom'" class="px-5 py-3 flex justify-around text-xs" :class="compactNavigationClass('{{ $option }}')"><span>Beranda</span><span>Proyek</span><span>Aktivitas</span><span>Profil</span></div>
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
        <section class="mt-6 rounded-3xl border border-gray-200 bg-white/70 p-5 dark:border-gray-700 dark:bg-gray-800/60">
            <div class="flex items-start justify-between gap-4"><div><h2 class="font-bold text-gray-900 dark:text-white">Pilih palet warna</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Palet ini akan diterapkan pada konfigurasi gaya desain akhir.</p></div><span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400" x-text="selectedPalette ? optionLabel(selectedPalette) : 'Belum dipilih'"></span></div>
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                @foreach($paletteDetails as $paletteKey => $palette)
                    <button type="button" @click="choosePalette('{{ $paletteKey }}')" data-track-component="palette-{{ $paletteKey }}" data-option-id="{{ $paletteKey }}" class="rounded-2xl border-2 p-2 text-left transition hover:-translate-y-0.5" :class="selectedPalette === '{{ $paletteKey }}' ? 'border-indigo-500 ring-4 ring-indigo-500/15' : 'border-gray-200 dark:border-gray-700'"><div class="h-16 rounded-xl bg-gradient-to-br {{ $palette['classes'] }} p-2 text-xs font-semibold text-white">{{ $palette['label'] }}</div><div class="mt-2 flex gap-1">@foreach($palette['swatches'] as $swatch)<span class="h-2 flex-1 rounded-full" style="background-color: {{ $swatch }}"></span>@endforeach</div></button>
                @endforeach
            </div>
        </section>

        <div x-show="fullscreenStyle" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" :style="paletteVariables()" :class="[fullscreenBackgroundClass(), combinedFontClass(), combinedFontSizeClass()]" role="dialog" aria-modal="true" @keydown.escape.window="demoModal ? demoModal = null : closeFullscreen()">
            <div class="relative min-h-full flex flex-col">
                <header class="sticky top-0 z-50 min-h-18 shrink-0" :class="fullscreenNavigationClass()">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex flex-wrap items-center gap-3">
                        <button x-show="combinedPreferences.navigation_position === 'left'" type="button" aria-label="Buka atau tutup navigasi" class="w-11 h-11 shrink-0 flex items-center justify-center text-xl" :class="fullscreenIconButtonClass()" @click="sidebarOpen = !sidebarOpen" data-track-component="fullscreen-sidebar-toggle">☰</button>
                        <div class="min-w-0"><strong class="block text-base sm:text-lg">TPUIRF Workspace</strong><span class="block text-xs opacity-60 truncate" x-text="optionLabel(fullscreenStyle)"></span></div>
                        <nav x-show="combinedPreferences.navigation_position === 'top'" class="order-3 sm:order-none w-full sm:w-auto sm:ml-auto flex items-center gap-1 overflow-x-auto rounded-xl border border-current/10 bg-black/5 p-1 dark:bg-white/5"><template x-for="item in ['Beranda', 'Proyek', 'Aktivitas', 'Profil']" :key="item"><button type="button" class="min-w-[105px] shrink-0 px-3 py-2 flex items-center justify-center gap-2 text-xs sm:text-sm font-semibold transition-colors" :class="fullscreenNavItemClass(item)" @click="activateDemoNav(item)" :data-track-component="`fullscreen-nav-${item.toLowerCase()}`"><i :class="combinedIconClass() + ' ' + demoIcon(item)"></i><span class="truncate" x-text="item"></span></button></template></nav>
                        <button type="button" aria-label="Tutup pratinjau layar penuh" class="w-11 h-11 shrink-0 ml-auto flex items-center justify-center text-xl" :class="fullscreenIconButtonClass()" @click="closeFullscreen()">×</button>
                    </div>
                </header>

                <div x-show="combinedPreferences.navigation_position === 'left' && sidebarOpen" x-transition.opacity class="fixed inset-0 top-[72px] z-30 bg-slate-950/30 lg:hidden" @click="sidebarOpen = false"></div>
                <aside x-show="combinedPreferences.navigation_position === 'left' && sidebarOpen" x-transition class="fixed left-0 top-[72px] bottom-0 z-40 w-72 overflow-y-auto p-5" :class="fullscreenNavigationClass()">
                    <p class="px-3 text-xs font-semibold uppercase tracking-[.18em] opacity-50">Menu Utama</p>
                    <nav class="mt-5 space-y-3"><template x-for="item in ['Beranda', 'Proyek', 'Aktivitas', 'Profil']" :key="item">                    <button type="button" class="w-full px-4 py-3 text-left flex items-center gap-3" :class="fullscreenNavItemClass(item)" @click="activateDemoNav(item)" :data-track-component="`fullscreen-nav-${item.toLowerCase()}`"><i class="text-xl w-6 text-center" :class="combinedIconClass() + ' ' + demoIcon(item)"></i><span class="block w-full truncate text-center" x-text="item"></span></button></template></nav>
                    <div class="mt-8 p-4" :class="fullscreenSurfaceClass()"><p class="text-xs opacity-60">Penyimpanan tim</p><div class="mt-3 h-2 overflow-hidden rounded-full bg-black/10"><div class="h-full w-2/3 bg-current opacity-60"></div></div><p class="mt-2 text-xs">6,4 GB dari 10 GB</p></div>
                </aside>

                 <main class="flex-1 transition-[margin]" :class="[combinedDensityClass(), combinedPreferences.navigation_position === 'left' && sidebarOpen ? 'lg:ml-72' : '', combinedPreferences.navigation_position === 'bottom' ? 'pb-32' : '']">
                    <div class="max-w-7xl mx-auto">
                        <div><p class="text-sm opacity-55" x-text="demoActiveNav + ' · Ringkasan hari ini'"></p><h2 class="mt-1 text-2xl sm:text-4xl font-bold">Selamat datang di ruang kerja Anda</h2><p class="mt-2 max-w-2xl opacity-70">Pantau eksperimen, baca insight terbaru, dan kelola pekerjaan tim dalam satu tempat.</p></div>
                        <div class="mt-6 flex flex-col sm:flex-row gap-3"><label class="flex-1"><span class="sr-only">Cari proyek</span><input type="search" class="w-full px-4 py-3" :class="fullscreenInputClass()" placeholder="Cari proyek, artikel, atau anggota tim..." @input.debounce.400ms="showDemoToast('Hasil pencarian diperbarui')"></label><button type="button" class="px-5 py-3 font-semibold" :class="fullscreenButtonClass(true)" @click="openDemoModal('Buat Proyek Baru')">+ Proyek Baru</button></div>
                        <div class="grid grid-cols-2 xl:grid-cols-4" :class="combinedGridGapClass()"><template x-for="stat in [{label:'Proyek aktif',value:'12'}, {label:'Tugas selesai',value:'84'}, {label:'Anggota tim',value:'18'}, {label:'Progres',value:'76%'}]" :key="stat.label"><button type="button" class="text-left" :class="[fullscreenSurfaceClass(), combinedCardPaddingClass()]" @click="openDemoModal(stat.label)"><p class="text-xs sm:text-sm opacity-60" x-text="stat.label"></p><p class="mt-2 text-2xl sm:text-3xl font-bold" x-text="stat.value"></p><p class="mt-3 text-xs" :class="fullscreenTextActionClass()">Lihat detail</p></button></template></div>
                        <div class="grid lg:grid-cols-[1.5fr_1fr]" :class="combinedGridGapClass()">
                            <section :class="[fullscreenSurfaceClass(), combinedCardPaddingClass()]"><div class="flex items-center justify-between"><h3 class="text-xl font-bold">Proyek Terbaru</h3><button type="button" class="font-semibold" :class="fullscreenTextActionClass()" @click="showDemoToast('Semua proyek ditampilkan')">Lihat semua</button></div><div class="mt-4 space-y-3"><template x-for="project in ['Riset Antarmuka Adaptif', 'Dashboard Analitik', 'Studi Aksesibilitas']" :key="project"><button type="button" class="w-full flex items-center justify-between gap-4 p-4 text-left" :class="fullscreenListItemClass()" @click="openDemoModal(project)"><span><strong x-text="project"></strong><small class="block mt-1 opacity-60">Diperbarui hari ini</small></span><span :class="fullscreenTextActionClass()">Buka</span></button></template></div></section>
                            <section :class="[fullscreenSurfaceClass(), combinedCardPaddingClass()]"><h3 class="text-xl font-bold">Aktivitas Tim</h3><div class="mt-5 space-y-5"><template x-for="activity in ['Nadia menyelesaikan evaluasi', 'Raka mengunggah dataset', 'Sinta menambahkan komentar']" :key="activity"><button type="button" class="flex gap-3 text-left items-center" @click="showDemoToast(activity)"><span class="w-9 h-9 shrink-0 flex items-center justify-center" :class="fullscreenIconBadgeClass()">•</span><span x-text="activity"></span></button></template></div></section>
                        </div>

                        <section :class="[fullscreenSurfaceClass(), combinedCardPaddingClass(), combinedGridGapClass()]"><div class="flex items-end justify-between gap-4"><div><p class="text-sm opacity-55" x-text="demoArticle.category"></p><h3 class="mt-1 text-2xl font-bold" x-text="demoArticle.title"></h3></div></div>
                            <div class="mt-5 w-full h-48 sm:h-64 rounded-xl" :class="demoArticle.cover"></div>
                            <div class="mt-6 space-y-4 opacity-80 leading-relaxed text-sm sm:text-base text-justify">
                                <p x-text="demoArticle.content[0]"></p>
                                <p x-text="demoArticle.content[1]"></p>
                                <p x-text="demoArticle.content[2]"></p>
                                <p x-text="demoArticle.content[3]"></p>
                                <p x-text="demoArticle.content[4]"></p>
                            </div>
                            <button type="button" class="mt-8 px-5 py-3 font-semibold" :class="fullscreenButtonClass(false)" @click="showDemoToast('Artikel disimpan')">Simpan artikel ini</button>
                        </section>
                    </div>
                </main>

                <nav x-show="combinedPreferences.navigation_position === 'bottom'" class="fixed bottom-4 sm:bottom-8 left-1/2 -translate-x-1/2 z-30 w-[calc(100%-2rem)] sm:w-auto sm:min-w-[400px] px-2 py-2 rounded-2xl flex justify-around shadow-2xl backdrop-blur-xl" :class="fullscreenNavigationClass()"><template x-for="item in ['Beranda', 'Proyek', 'Aktivitas', 'Profil']" :key="item"><button type="button" class="min-w-0 w-full px-4 py-2 text-xs sm:text-sm flex flex-col items-center justify-center gap-1" :class="fullscreenNavItemClass(item)" @click="activateDemoNav(item)" :data-track-component="`fullscreen-nav-${item.toLowerCase()}`"><i class="text-xl h-6 w-6 flex items-center justify-center" :class="combinedIconClass() + ' ' + demoIcon(item)"></i><span class="block w-full truncate text-center" x-text="item"></span></button></template></nav>

                <div x-show="demoToast" x-transition class="fixed right-4 bottom-24 z-40 px-5 py-3 text-sm" :class="fullscreenToastClass()" x-text="demoToast"></div>
                <div x-show="demoModal" x-cloak class="fixed inset-0 z-50 p-4 flex items-center justify-center" :class="fullscreenModalOverlayClass()" @click.self="demoModal = null">
                    <div class="w-full max-w-lg overflow-hidden" :class="fullscreenSurfaceClass()" role="dialog" aria-modal="true" aria-labelledby="demo-modal-title">
                        <div class="p-5 sm:p-6 flex items-start gap-4 border-b border-current/10"><span class="w-11 h-11 shrink-0 flex items-center justify-center" :class="fullscreenIconBadgeClass()">✓</span><div class="min-w-0"><p class="text-xs font-semibold uppercase tracking-[.15em] opacity-50">Pratinjau Interaksi</p><h3 id="demo-modal-title" class="mt-1 text-xl sm:text-2xl font-bold break-words" x-text="demoModal"></h3></div><button type="button" aria-label="Tutup modal" class="w-10 h-10 ml-auto shrink-0" :class="fullscreenIconButtonClass()" @click="demoModal = null">×</button></div>
                        <div class="p-5 sm:p-6"><p class="leading-relaxed opacity-70">Komponen ini merespons seperti dashboard nyata. Aksi hanya disimulasikan dan tidak mengubah data eksperimen Anda.</p><div class="mt-5 p-4" :class="fullscreenListItemClass()"><p class="text-sm font-semibold">Yang akan terjadi</p><p class="mt-1 text-sm opacity-65">Sistem menampilkan konfirmasi visual dan mencatat interaksi pada pratinjau.</p></div></div>
                        <div class="p-5 sm:p-6 pt-0 flex flex-col-reverse sm:flex-row sm:justify-end gap-3"><button type="button" class="px-5 py-3 font-semibold" :class="fullscreenButtonClass(false)" @click="demoModal = null">Batal</button><button type="button" class="px-5 py-3 font-semibold" :class="fullscreenButtonClass(true)" @click="confirmDemoModal()">Konfirmasi Aksi</button></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-8 sticky bottom-4 z-40 flex flex-col sm:flex-row items-center justify-between gap-4 rounded-2xl border border-white/60 dark:border-gray-700 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl p-4 shadow-xl">
        <p class="text-sm text-gray-600 dark:text-gray-300" x-text="selectionSummary()"></p>
        <div class="w-full sm:w-auto flex flex-col-reverse sm:flex-row gap-3">
            @if($previousTaskKey)
                <button type="button" @click="goBack()" :disabled="submitting" class="w-full sm:w-auto px-6 py-3 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-semibold hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50" data-track-component="task-back">
                    Kembali
                </button>
            @endif
            <button type="button" @click="submitTask()" :disabled="submitting" class="w-full sm:w-auto px-8 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-500 text-white font-semibold disabled:opacity-50" data-track-component="task-submit">
                <span x-text="submitting ? 'Menyimpan…' : (isReviewing ? 'Simpan Perubahan' : '{{ $task['sequence'] < count($experiment['task_order']) ? 'Simpan & Lanjut' : 'Lanjut ke Evaluasi' }}')"></span>
            </button>
        </div>
    </div>
    <p x-show="errorMessage" x-text="errorMessage" class="mt-3 text-center text-sm text-red-600"></p>
</div>
@endsection

@push('styles')
<style>
    .tpuirf-palette-bg { background-color: var(--palette-background); }
    .tpuirf-palette-deep { background-color: var(--palette-deep); }
    .tpuirf-palette-soft { background-color: var(--palette-soft); }
    .tpuirf-palette-primary { background-color: var(--palette-primary); }
    .tpuirf-palette-accent { background-color: var(--palette-accent); }
    .tpuirf-palette-primary-text { color: var(--palette-primary); }
    .tpuirf-palette-deep-text { color: var(--palette-deep); }
    .tpuirf-palette-primary:hover { background-color: var(--palette-accent); }
    .tpuirf-palette-soft:hover { background-color: var(--palette-background); }
    .tpuirf-palette-primary-text:hover { color: var(--palette-accent); }
</style>
@endpush

@push('scripts')
<script>
function researchPreferenceTask(config) {
    return {
        ...config,
        selectedOption: config.initialSelectedOption || null,
        fontFamily: config.initialSelectionPayload?.font_family || null,
        fontSize: config.initialSelectionPayload?.font_size || null,
        selectedStyles: config.initialSelectionPayload?.design_styles || [],
        selectedPalette: config.initialSelectionPayload?.color_palette || null,
        expandedStyle: null,
        fullscreenStyle: null,
        sidebarOpen: true,
        demoActiveNav: 'Beranda',
        demoModal: null,
        demoToast: '',
        demoToastTimer: null,
        demoArticle: {
            category: 'Riset Antarmuka',
            title: 'Membaca pola interaksi tanpa kehilangan konteks pengguna',
            cover: 'bg-gradient-to-br from-indigo-300 via-purple-300 to-rose-300',
            content: [
                'Pelajari cara menggabungkan data klik, waktu aktif, dan transisi untuk menemukan pola yang benar-benar bermakna. Analisis kuantitatif tanpa konteks kualitatif sering kali menyesatkan karena satu klik cepat bisa berarti efisiensi, atau sebaliknya, kebingungan antarmuka.',
                'Dalam eksperimen skala besar, randomisasi opsi adalah kunci untuk mencegah bias posisi. Sering kali, opsi pertama menerima lebih banyak interaksi hanya karena letaknya yang mudah dijangkau, bukan karena opsi tersebut merupakan pilihan yang secara sadar disukai partisipan.',
                'Perhatikan juga interaksi gulir (scroll). Kedalaman dan durasi scroll menceritakan tingkat ketertarikan. Pengguna yang membaca artikel sampai akhir dan berhenti sejenak pada paragraf tertentu menunjukkan keterlibatan yang jauh lebih berharga daripada kunjungan singkat (bounce).',
                'Selain itu, jangan abaikan metrik aksesibilitas. Kontras yang terlalu rendah atau font yang terlalu kecil dapat memanipulasi data durasi tugas. Partisipan menghabiskan waktu lebih lama bukan karena mereka memikirkan pilihan, tetapi karena mereka kesulitan memecahkan sandi visual di layar.',
                'Pada akhirnya, pastikan Anda memisahkan "waktu diam" (idle time) dari waktu aktif. Waktu di mana tab disembunyikan atau partisipan tidak menggerakkan tetikus sama sekali tidak relevan dengan kognisi tugas dan harus dikeluarkan dari analisis durasi murni.'
            ]
        },
        previewOpenedAt: null,
        activeMs: 0,
        idleMs: 0,
        changeCount: 0,
        submitting: false,
        errorMessage: '',
        lastFlushError: '',
        queue: [],
        sequenceNumber: 0,
        startedAt: performance.now(),
        lastTickAt: performance.now(),
        lastActivityAt: performance.now(),
        lastEventAt: null,
        lastClickAt: null,
        lastComponentId: null,
        scrollSession: null,
        scrollTimer: null,
        flushTimer: null,
        themeTransition: null,
        themeFallbackTimer: null,
        themePointer: null,

        init() {
            this.restoreQueue();
            this.restoreDurations();
            const storedSequence = Number(localStorage.getItem(`tpuirf-sequence-${this.experimentId}`) || 0);
            const queuedSequence = this.queue.reduce((maximum, event) => Math.max(maximum, Number(event.sequence_number) || 0), 0);
            this.sequenceNumber = Math.max(this.initialSequence, storedSequence, queuedSequence);
            this.bindActivityTracking();
            if (this.taskKey === 'theme' && ['light', 'dark'].includes(this.selectedOption)) {
                this.applyThemePreview(this.selectedOption);
            }
            this.logEvent('task_viewed', null, null, { sequence: this.sequence });
            this.flushTimer = setInterval(() => this.flushEvents(), 5000);
            setInterval(() => this.updateDurations(), 1000);
        },

        bindActivityTracking() {
            ['pointerdown', 'keydown'].forEach(type => document.addEventListener(type, () => this.markActivity(), { passive: true }));
            document.addEventListener('click', event => {
                const target = event.target.closest('[data-track-component]');
                if (!target) return;
                this.markActivity();
                this.logEvent('component_clicked', target.dataset.trackComponent, target.dataset.optionId || null, {}, true, event.pointerType || 'mouse');
            });
            window.addEventListener('scroll', () => this.trackScroll(), { passive: true });
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.logEvent('page_hidden');
                    this.flushEvents(true);
                } else {
                    this.markActivity();
                }
            });
        },

        markActivity() { this.lastActivityAt = performance.now(); },

        updateDurations() {
            const now = performance.now();
            const delta = now - this.lastTickAt;
            if (now - this.lastActivityAt > this.idleThresholdMs) this.idleMs += delta;
            else this.activeMs += delta;
            this.lastTickAt = now;
            this.persistDurations();
        },

        choose(option, event = null) {
            const previous = this.selectedOption;
            this.selectedOption = option;
            if (this.taskKey === 'theme') this.applyThemePreview(option, event);
            this.changeCount++;
            this.logEvent('selection_changed', `selection-${option}`, option, {
                previous_option: previous,
                page_theme_applied: this.taskKey === 'theme',
            });
        },

        rememberThemePointer(event) {
            if (!Number.isFinite(event?.clientX) || !Number.isFinite(event?.clientY)) return;
            this.themePointer = { x: event.clientX, y: event.clientY };
        },

        applyThemePreview(option, event = null) {
            if (!['light', 'dark'].includes(option)) return;

            const darkMode = option === 'dark';
            const applyTheme = () => {
                window.tpuirfTheme.apply(darkMode);
                window.dispatchEvent(new CustomEvent('tpuirf-theme-preview', {
                    detail: { darkMode },
                }));
            };
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (!event || reducedMotion) {
                applyTheme();
                return;
            }

            const trigger = event.currentTarget;
            const bounds = trigger?.getBoundingClientRect();
            const clickHasPosition = Number.isFinite(event.clientX)
                && Number.isFinite(event.clientY)
                && (event.clientX !== 0 || event.clientY !== 0);
            const pointer = clickHasPosition ? { x: event.clientX, y: event.clientY } : this.themePointer;
            const fallbackX = (bounds?.left ?? 0) + ((bounds?.width ?? window.innerWidth) / 2);
            const fallbackY = (bounds?.top ?? 0) + ((bounds?.height ?? window.innerHeight) / 2);
            const originX = Math.min(window.innerWidth, Math.max(0, pointer?.x ?? fallbackX));
            const originY = Math.min(window.innerHeight, Math.max(0, pointer?.y ?? fallbackY));
            this.themePointer = null;
            const radius = Math.ceil(Math.hypot(
                Math.max(originX, window.innerWidth - originX),
                Math.max(originY, window.innerHeight - originY),
            )) + 56;
            const root = document.documentElement;

            root.style.setProperty('--tpuirf-theme-origin-x', `${originX}px`);
            root.style.setProperty('--tpuirf-theme-origin-y', `${originY}px`);
            root.style.setProperty('--tpuirf-theme-radius', `${radius}px`);
            root.style.setProperty('--tpuirf-theme-edge-scale', String(radius / 60));

            if (typeof document.startViewTransition !== 'function') {
                clearTimeout(this.themeFallbackTimer);
                root.classList.add('tpuirf-theme-fallback');
                const edgeWave = this.createThemeEdgeWave(originX, originY, radius, darkMode, true);
                applyTheme();
                this.themeFallbackTimer = setTimeout(() => {
                    root.classList.remove('tpuirf-theme-fallback');
                    edgeWave.remove();
                }, 1250);
                return;
            }

            this.themeTransition?.skipTransition?.();
            let edgeWave = null;
            const transition = document.startViewTransition(() => {
                edgeWave = this.createThemeEdgeWave(originX, originY, radius, darkMode);
                applyTheme();
            });
            this.themeTransition = transition;
            transition.finished.finally(() => {
                edgeWave?.remove();
                if (this.themeTransition !== transition) return;
                this.themeTransition = null;
                root.style.removeProperty('--tpuirf-theme-origin-x');
                root.style.removeProperty('--tpuirf-theme-origin-y');
                root.style.removeProperty('--tpuirf-theme-radius');
                root.style.removeProperty('--tpuirf-theme-edge-scale');
            });
        },

        createThemeEdgeWave(originX, originY, radius, darkMode, fallback = false) {
            document.querySelector('[data-tpuirf-theme-edge-wave]')?.remove();
            const size = 120;
            const edgeWave = document.createElement('span');
            edgeWave.dataset.tpuirfThemeEdgeWave = '';
            edgeWave.setAttribute('aria-hidden', 'true');
            edgeWave.className = `tpuirf-theme-edge-wave tpuirf-theme-edge-wave--${darkMode ? 'dark' : 'light'}${fallback ? ' tpuirf-theme-edge-wave--fallback' : ''}`;
            edgeWave.style.left = `${originX - (size / 2)}px`;
            edgeWave.style.top = `${originY - (size / 2)}px`;
            edgeWave.style.setProperty('--tpuirf-theme-edge-scale', String(radius / (size / 2)));
            document.body.appendChild(edgeWave);
            return edgeWave;
        },

        selectionChanged(option) {
            this.changeCount++;
            this.logEvent('selection_changed', `selection-${option}`, option, { font_family: this.fontFamily, font_size: this.fontSize });
        },

        toggleStyleSelection(style) {
            const previousStyles = [...this.selectedStyles];
            if (this.isStyleSelected(style)) {
                this.selectedStyles = this.selectedStyles.filter(selectedStyle => selectedStyle !== style);
            } else {
                this.selectedStyles = [...this.selectedStyles, style];
            }
            this.changeCount++;
            this.logEvent('selection_changed', `selection-${style}`, style, {
                previous_design_styles: previousStyles,
                design_styles: this.selectedStyles,
                action: this.isStyleSelected(style) ? 'selected' : 'deselected',
            });
        },

        isStyleSelected(style) { return this.selectedStyles.includes(style); },

        choosePalette(palette) {
            const previous = this.selectedPalette;
            this.selectedPalette = palette;
            this.changeCount++;
            this.logEvent('selection_changed', `palette-${palette}`, palette, {
                previous_color_palette: previous,
                color_palette: palette,
            });
        },

        togglePreview(style) {
            const now = performance.now();
            if (this.expandedStyle) {
                this.logEvent('style_preview_collapsed', `design-${this.expandedStyle}-accordion`, this.expandedStyle, { dwell_ms: Math.round(now - this.previewOpenedAt) });
            }
            if (this.expandedStyle === style) {
                this.expandedStyle = null;
                this.previewOpenedAt = null;
                return;
            }
            this.expandedStyle = style;
            this.previewOpenedAt = now;
            this.logEvent('style_preview_expanded', `design-${style}-accordion`, style);
        },

        openFullscreen(style) {
            this.fullscreenStyle = style;
            this.sidebarOpen = this.combinedPreferences.navigation_position === 'left';
            this.demoActiveNav = 'Beranda';
            this.demoModal = null;
            this.demoToast = '';
            document.body.style.overflow = 'hidden';
            this.logEvent('style_preview_fullscreened', `design-${style}-fullscreen`, style);
        },

        closeFullscreen() {
            this.fullscreenStyle = null;
            this.demoModal = null;
            document.body.style.overflow = '';
        },

        activateDemoNav(item) {
            this.demoActiveNav = item;
            this.showDemoToast(`Menu ${item} dibuka`);
        },

        openDemoModal(title) { this.demoModal = title; },

        confirmDemoModal() {
            const title = this.demoModal;
            this.demoModal = null;
            this.showDemoToast(`${title} berhasil disimulasikan`);
        },

        showDemoToast(message) {
            this.demoToast = message;
            clearTimeout(this.demoToastTimer);
            this.demoToastTimer = setTimeout(() => { this.demoToast = ''; }, 2200);
        },

        trackScroll() {
            this.markActivity();
            const now = performance.now();
            if (!this.scrollSession) {
                this.scrollSession = { startY: window.scrollY, startAt: now, maxY: window.scrollY, lastY: window.scrollY, directionChanges: 0, direction: null };
                this.logEvent('scroll_started', 'window-scroll', null, { start_y: window.scrollY });
            }
            const currentDirection = window.scrollY >= this.scrollSession.lastY ? 'down' : 'up';
            if (this.scrollSession.direction && this.scrollSession.direction !== currentDirection) this.scrollSession.directionChanges++;
            this.scrollSession.direction = currentDirection;
            this.scrollSession.lastY = window.scrollY;
            this.scrollSession.maxY = Math.max(this.scrollSession.maxY, window.scrollY);
            clearTimeout(this.scrollTimer);
            this.scrollTimer = setTimeout(() => this.endScroll(), 400);
        },

        endScroll() {
            if (!this.scrollSession) return;
            const session = this.scrollSession;
            const documentHeight = Math.max(document.documentElement.scrollHeight - window.innerHeight, 1);
            this.logEvent('scroll_ended', 'window-scroll', null, {
                start_y: session.startY,
                end_y: window.scrollY,
                distance_px: Math.abs(window.scrollY - session.startY),
                duration_ms: Math.round(performance.now() - session.startAt),
                direction: session.direction,
                max_scroll_depth_percent: Math.round((session.maxY / documentHeight) * 10000) / 100,
                direction_change_count: session.directionChanges,
            });
            this.scrollSession = null;
        },

        logEvent(eventType, componentId = null, optionId = null, payload = {}, isClick = false, inputMethod = 'unknown') {
            const now = performance.now();
            this.sequenceNumber++;
            try { localStorage.setItem(`tpuirf-sequence-${this.experimentId}`, this.sequenceNumber); } catch (_) {}
            const event = {
                event_uuid: this.eventUuid(),
                sequence_number: this.sequenceNumber,
                task_key: this.taskKey,
                option_id: optionId,
                component_id: componentId,
                event_type: eventType,
                client_timestamp: new Date().toISOString(),
                elapsed_task_ms: Math.round(this.activeMs + this.idleMs + (now - this.lastTickAt)),
                elapsed_experiment_ms: Math.max(0, Date.now() - Date.parse(this.experimentStartedAt)),
                delta_previous_event_ms: this.lastEventAt === null ? null : Math.round(now - this.lastEventAt),
                delta_previous_click_ms: isClick && this.lastClickAt !== null ? Math.round(now - this.lastClickAt) : null,
                previous_component_id: isClick ? this.lastComponentId : null,
                viewport_width: window.innerWidth,
                viewport_height: window.innerHeight,
                input_method: inputMethod,
                payload,
            };
            this.lastEventAt = now;
            if (isClick) { this.lastClickAt = now; this.lastComponentId = componentId; }
            this.queue.push(event);
            this.persistQueue();
            if (this.queue.length >= 10) this.flushEvents();
        },

        persistQueue() {
            try { localStorage.setItem(`tpuirf-events-${this.experimentId}`, JSON.stringify(this.queue)); } catch (_) {}
        },
        restoreQueue() {
            try { this.queue = JSON.parse(localStorage.getItem(`tpuirf-events-${this.experimentId}`) || '[]'); }
            catch (_) { this.queue = []; }
        },
        persistDurations() {
            try {
                localStorage.setItem(`tpuirf-duration-${this.experimentId}-${this.taskKey}`, JSON.stringify({
                    activeMs: Math.round(this.activeMs),
                    idleMs: Math.round(this.idleMs),
                }));
            } catch (_) {}
        },
        restoreDurations() {
            try {
                const duration = JSON.parse(localStorage.getItem(`tpuirf-duration-${this.experimentId}-${this.taskKey}`) || '{}');
                this.activeMs = Math.max(0, Number(duration.activeMs) || 0);
                this.idleMs = Math.max(0, Number(duration.idleMs) || 0);
            } catch (_) {
                this.activeMs = 0;
                this.idleMs = 0;
            }
        },

        async flushEvents(keepalive = false) {
            if (!this.queue.length) return true;
            const batch = this.queue.slice(0, 100);
            try {
                const response = await fetch(this.eventsUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ events: batch }),
                    keepalive,
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) {
                    this.lastFlushError = result.message || `Server menolak data interaksi (HTTP ${response.status}).`;
                    return false;
                }
                const sentIds = new Set(batch.map(event => event.event_uuid));
                this.queue = this.queue.filter(event => !sentIds.has(event.event_uuid));
                const serverSequence = Number(result.data?.last_sequence_number || 0);
                this.sequenceNumber = Math.max(this.sequenceNumber, serverSequence);
                try { localStorage.setItem(`tpuirf-sequence-${this.experimentId}`, this.sequenceNumber); } catch (_) {}
                this.persistQueue();
                this.lastFlushError = '';
                return true;
            } catch (_) {
                this.lastFlushError = 'Server tidak dapat dijangkau.';
                return false;
            }
        },

        async goBack() {
            if (!this.previousTaskUrl || this.submitting) return;
            await new Promise(resolve => setTimeout(resolve, 0));
            await this.flushEvents(true);
            window.location.href = this.previousTaskUrl;
        },

        async submitTask() {
            this.errorMessage = '';
            if (this.taskKey === 'typography' && (!this.fontFamily || !this.fontSize)) {
                this.errorMessage = 'Pilih keluarga font dan ukuran terlebih dahulu.'; return;
            }
            if (this.taskKey === 'design_style' && (this.selectedStyles.length === 0 || !this.selectedPalette)) {
                this.errorMessage = 'Pilih minimal satu gaya desain dan satu palet warna terlebih dahulu.'; return;
            }
            if (!['typography', 'design_style'].includes(this.taskKey) && !this.selectedOption) {
                this.errorMessage = 'Pilih salah satu opsi terlebih dahulu.'; return;
            }
            this.submitting = true;
            this.updateDurations();
            this.endScroll();
            if (this.taskKey === 'design_style' && this.expandedStyle && this.previewOpenedAt !== null) {
                this.logEvent('style_preview_collapsed', `design-${this.expandedStyle}-accordion`, this.expandedStyle, {
                    dwell_ms: Math.round(performance.now() - this.previewOpenedAt),
                    reason: 'task_submitted',
                });
                this.previewOpenedAt = null;
            }
            this.logEvent('task_submitted', 'task-submit', this.selectedOption, {
                active_duration_ms: Math.round(this.activeMs),
                idle_duration_ms: Math.round(this.idleMs),
                design_styles: this.taskKey === 'design_style' ? this.selectedStyles : undefined,
                color_palette: this.taskKey === 'design_style' ? this.selectedPalette : undefined,
            });
            const eventsFlushed = await this.flushEvents();
            if (!eventsFlushed) {
                this.errorMessage = `Data interaksi belum berhasil dikirim: ${this.lastFlushError} Coba lagi agar data penelitian tidak hilang.`;
                this.submitting = false;
                return;
            }
            const body = {
                active_duration_ms: Math.round(this.activeMs),
                idle_duration_ms: Math.round(this.idleMs),
                change_count: this.changeCount,
            };
            if (this.taskKey === 'typography') body.selection_payload = { font_family: this.fontFamily, font_size: this.fontSize };
            else if (this.taskKey === 'design_style') body.selection_payload = { design_styles: this.selectedStyles, color_palette: this.selectedPalette };
            else body.selected_option = this.selectedOption;
            try {
                const response = await fetch(this.selectionUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(body),
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Pilihan tidak dapat disimpan.');
                localStorage.removeItem(`tpuirf-duration-${this.experimentId}-${this.taskKey}`);
                if (result.data.status === 'evaluation') window.location.href = this.evaluationUrl;
                else window.location.href = this.taskPageUrl.replace('__TASK__', result.data.current_task);
            } catch (error) {
                this.errorMessage = error.message;
                this.submitting = false;
            }
        },

        selectionSummary() {
            if (this.taskKey === 'typography') return this.fontFamily && this.fontSize ? `Pilihan: ${this.optionLabel(this.fontFamily)} · ${this.optionLabel(this.fontSize)}` : 'Belum ada pilihan';
            if (this.taskKey === 'design_style') return this.selectedStyles.length && this.selectedPalette ? `${this.selectedStyles.length} gaya · Palet ${this.optionLabel(this.selectedPalette)}` : 'Pilih gaya desain dan palet warna';
            return this.selectedOption ? `Pilihan: ${this.optionLabel(this.selectedOption)}` : 'Belum ada pilihan';
        },
        optionLabel(option) { return this.optionLabels[option] || option || ''; },
        formatDuration(ms) { const seconds = Math.floor(ms / 1000); return `${Math.floor(seconds / 60)}:${String(seconds % 60).padStart(2, '0')}`; },
        fontFamilyClass() { return { sans: 'font-preview-inter', serif: 'font-preview-merriweather', high_legibility: 'font-preview-atkinson' }[this.fontFamily] || 'font-preview-inter'; },
        fontSizeClass() { return { small: 'text-sm', medium: 'text-base', large: 'text-lg' }[this.fontSize] || 'text-base'; },
        combinedPreferenceLabels() {
            return [
                `Tema: ${this.optionLabel(this.combinedPreferences.theme)}`,
                `Palet: ${this.optionLabel(this.combinedPreferences.color_palette)}`,
                `Font: ${this.optionLabel(this.combinedPreferences.font_family)}`,
                `Ukuran: ${this.optionLabel(this.combinedPreferences.font_size)}`,
                `Tata letak: ${this.optionLabel(this.combinedPreferences.layout_density)}`,
                `Navigasi: ${this.optionLabel(this.combinedPreferences.navigation_position)}`,
                `Ikon: ${this.optionLabel(this.combinedPreferences.icon_style)}`,
            ];
        },
        combinedFontClass() { return { sans: 'font-preview-inter', serif: 'font-preview-merriweather', high_legibility: 'font-preview-atkinson' }[this.combinedPreferences.font_family] || 'font-preview-inter'; },
        combinedFontSizeClass() { return { small: 'text-sm', medium: 'text-base', large: 'text-lg' }[this.combinedPreferences.font_size] || 'text-base'; },
        combinedDensityClass() { return { compact: 'p-3 sm:p-4', comfortable: 'p-5 sm:p-7', spacious: 'p-8 sm:p-12' }[this.combinedPreferences.layout_density] || 'p-5 sm:p-7'; },
        combinedCardPaddingClass() { return { compact: 'p-4', comfortable: 'p-6', spacious: 'p-8' }[this.combinedPreferences.layout_density] || 'p-6'; },
        combinedGridGapClass() { return { compact: 'gap-3 mt-3', comfortable: 'gap-5 mt-5', spacious: 'gap-8 mt-8' }[this.combinedPreferences.layout_density] || 'gap-5 mt-5'; },
        combinedIconClass() {
            const baseClass = {
                outline: 'fa-solid icon-outline-preview',
                filled: 'fa-solid',
                duotone: 'fa-solid icon-duotone-preview',
            }[this.combinedPreferences.icon_style] || 'fa-solid icon-outline-preview';
            return `fa-fw ${baseClass}`;
        },
        demoIcon(item) { return { Beranda: 'fa-compass', Proyek: 'fa-folder', Aktivitas: 'fa-clock', Profil: 'fa-user' }[item] || 'fa-circle'; },
        paletteVariables() {
            const palettes = {
                blue: { primary: '#2563eb', accent: '#06b6d4', soft: '#dbeafe', background: '#eff6ff', deep: '#172554' },
                green: { primary: '#059669', accent: '#14b8a6', soft: '#d1fae5', background: '#ecfdf5', deep: '#064e3b' },
                purple: { primary: '#7c3aed', accent: '#d946ef', soft: '#ede9fe', background: '#faf5ff', deep: '#581c87' },
                orange: { primary: '#ea580c', accent: '#f59e0b', soft: '#ffedd5', background: '#fff7ed', deep: '#7c2d12' },
                rose: { primary: '#e11d48', accent: '#ec4899', soft: '#ffe4e6', background: '#fff1f2', deep: '#881337' },
                neutral: { primary: '#475569', accent: '#64748b', soft: '#e2e8f0', background: '#f8fafc', deep: '#1e293b' },
            };
            const palette = palettes[this.selectedPalette || this.combinedPreferences.color_palette] || palettes.blue;
            return {
                '--palette-primary': palette.primary,
                '--palette-accent': palette.accent,
                '--palette-soft': palette.soft,
                '--palette-background': palette.background,
                '--palette-deep': palette.deep,
            };
        },
        previewBackgroundClass(style = null) {
            if (style === 'high_contrast_brutalist') return this.combinedPreferences.theme === 'dark' ? 'bg-slate-900' : 'bg-stone-200';
            return 'tpuirf-palette-bg';
        },
        compactSurfaceClass(style, nested = false) {
            const dark = this.combinedPreferences.theme === 'dark';
            return {
                glassmorphism: dark ? 'rounded-2xl bg-gradient-to-b from-white/10 to-white/5 text-white backdrop-blur-3xl backdrop-saturate-150 border border-white/10 border-t-white/30 border-l-white/20 shadow-[0_8px_32px_rgba(0,0,0,0.5)]' : 'rounded-2xl bg-gradient-to-b from-white/60 to-white/30 text-slate-900 backdrop-blur-3xl backdrop-saturate-150 border border-white/80 border-t-white border-l-white shadow-[0_8px_32px_rgba(31,38,135,0.1)]',
                neumorphism: dark ? `rounded-2xl bg-slate-800 text-slate-100 border-0 ${nested ? 'shadow-[inset_5px_5px_10px_#0f172a,inset_-5px_-5px_10px_#334155]' : 'shadow-[10px_10px_22px_#0f172a,-10px_-10px_22px_#334155]'}` : `rounded-2xl bg-slate-200 text-slate-900 border-0 ${nested ? 'shadow-[inset_5px_5px_10px_#b8c0cc,inset_-5px_-5px_10px_#ffffff]' : 'shadow-[10px_10px_24px_#b8c0cc,-10px_-10px_24px_#ffffff]'}`,
                material_elevated: dark ? 'rounded-2xl tpuirf-palette-deep text-slate-100 shadow-xl' : 'rounded-2xl tpuirf-palette-bg text-slate-900 shadow-xl',
                high_contrast_brutalist: dark ? 'bg-slate-800 text-slate-100 border-4 border-black shadow-[7px_7px_0_#000]' : 'bg-stone-100 text-slate-900 border-4 border-slate-900 shadow-[7px_7px_0_#94a3b8]',
                minimal_flat: dark ? 'rounded-lg tpuirf-palette-deep text-slate-100 border-2 border-slate-700' : 'rounded-lg tpuirf-palette-bg text-slate-900 border-2 border-slate-300',
            }[style] || 'rounded-2xl bg-white text-slate-900';
        },
        compactNavigationClass(style) {
            const dark = this.combinedPreferences.theme === 'dark';
            return {
                glassmorphism: dark ? 'bg-white/5 text-white backdrop-blur-3xl backdrop-saturate-150 border-b border-white/10 shadow-[0_4px_16px_rgba(0,0,0,0.3)]' : 'bg-white/40 text-slate-900 backdrop-blur-3xl backdrop-saturate-150 border-b border-white/80 shadow-[0_4px_16px_rgba(31,38,135,0.05)]',
                neumorphism: dark ? 'bg-slate-800 text-slate-100 shadow-[inset_4px_4px_8px_#0f172a,inset_-4px_-4px_8px_#334155]' : 'bg-slate-200 text-slate-900 shadow-[inset_4px_4px_8px_#b8c0cc,inset_-4px_-4px_8px_#ffffff]',
                material_elevated: dark ? 'bg-slate-800 text-white shadow-lg' : 'bg-white text-slate-900 shadow-lg',
                high_contrast_brutalist: dark ? 'bg-slate-800 text-slate-100 border-b-4 border-black' : 'bg-stone-100 text-slate-900 border-b-4 border-slate-900',
                minimal_flat: dark ? 'bg-slate-900 text-white border-b-2 border-slate-700' : 'bg-white text-slate-900 border-b-2 border-slate-300',
            }[style] || 'tpuirf-palette-primary text-white';
        },
        compactButtonClass(style, primary) {
            const dark = this.combinedPreferences.theme === 'dark';
            if (style === 'glassmorphism') return primary ? 'rounded-xl tpuirf-palette-primary text-white backdrop-blur-md border border-white/30 shadow-lg transition-all' : 'rounded-xl bg-white/20 text-current backdrop-blur-md border border-white/50 border-t-white/80 hover:bg-white/40 hover:shadow-lg transition-all';
            if (style === 'neumorphism') return dark ? `rounded-xl bg-slate-800 text-indigo-400 transition-all duration-300 ${primary ? 'shadow-[6px_6px_12px_#0f172a,-6px_-6px_12px_#334155] hover:shadow-[2px_2px_4px_#0f172a,-2px_-2px_4px_#334155] hover:scale-[0.98]' : 'shadow-[inset_4px_4px_8px_#0f172a,inset_-4px_-4px_8px_#334155] hover:shadow-[inset_2px_2px_4px_#0f172a,inset_-2px_-2px_4px_#334155]'}` : `rounded-xl bg-slate-200 text-indigo-700 transition-all duration-300 ${primary ? 'shadow-[6px_6px_12px_#b8c0cc,-6px_-6px_12px_#ffffff] hover:shadow-[2px_2px_4px_#b8c0cc,-2px_-2px_4px_#ffffff] hover:scale-[0.98]' : 'shadow-[inset_4px_4px_8px_#b8c0cc,inset_-4px_-4px_8px_#ffffff] hover:shadow-[inset_2px_2px_4px_#b8c0cc,inset_-2px_-2px_4px_#ffffff]'}`;
            if (style === 'high_contrast_brutalist') return primary ? 'tpuirf-palette-accent text-slate-900 border-4 border-black shadow-[4px_4px_0_#000] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0_#000] transition-all' : (dark ? 'bg-slate-700 text-slate-100 border-4 border-black shadow-[4px_4px_0_#000] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0_#000] hover:bg-slate-600 transition-all' : 'bg-stone-100 text-slate-900 border-4 border-slate-900 shadow-[4px_4px_0_#94a3b8] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0_#94a3b8] hover:bg-stone-200 transition-all');
            if (style === 'minimal_flat') return primary ? 'rounded-lg tpuirf-palette-primary text-white hover:-translate-y-0.5 transition-transform' : 'rounded-lg border-2 border-current tpuirf-palette-soft transition-colors';
            return primary ? 'rounded-xl tpuirf-palette-primary text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all' : 'rounded-xl border border-current/20 shadow-sm tpuirf-palette-soft transition-colors';
        },
        compactSelectionButtonClass(style) {
            if (style === 'high_contrast_brutalist') return this.isStyleSelected(style) ? 'bg-rose-400 text-slate-900 border-4 border-black shadow-[4px_4px_0_#000] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0_#000] hover:bg-rose-500 transition-all' : 'bg-emerald-400 text-slate-900 border-4 border-black shadow-[4px_4px_0_#000] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0_#000] hover:bg-emerald-500 transition-all';
            if (style === 'glassmorphism') return this.isStyleSelected(style) ? 'rounded-xl tpuirf-palette-primary text-white backdrop-blur-md border border-white/50 border-t-white/80 shadow-lg transition-all' : 'rounded-xl tpuirf-palette-soft tpuirf-palette-deep-text border border-white/50 shadow-lg transition-all';
            if (style === 'neumorphism') return this.compactButtonClass(style, !this.isStyleSelected(style));
            return this.isStyleSelected(style) ? 'rounded-xl tpuirf-palette-primary text-white shadow-lg hover:-translate-y-0.5 transition-all' : 'rounded-xl tpuirf-palette-soft tpuirf-palette-deep-text shadow-lg transition-all';
        },
        eventUuid() {
            if (globalThis.crypto?.randomUUID) return globalThis.crypto.randomUUID();
            if (globalThis.crypto?.getRandomValues) {
                const bytes = globalThis.crypto.getRandomValues(new Uint8Array(16));
                bytes[6] = (bytes[6] & 0x0f) | 0x40;
                bytes[8] = (bytes[8] & 0x3f) | 0x80;
                const hex = [...bytes].map(byte => byte.toString(16).padStart(2, '0')).join('');
                return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
            }
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, character => {
                const random = Math.floor(Math.random() * 16);
                return (character === 'x' ? random : (random & 0x3) | 0x8).toString(16);
            });
        },
        fullscreenBackgroundClass() {
            const dark = this.combinedPreferences.theme === 'dark';
            if (this.fullscreenStyle === 'high_contrast_brutalist') return dark ? 'bg-slate-900 text-slate-100' : 'bg-stone-200 text-slate-900';
            if (this.fullscreenStyle === 'neumorphism') return dark ? 'bg-slate-800 text-slate-100' : 'bg-[#e3e8ef] text-slate-800';
            if (this.fullscreenStyle === 'glassmorphism') return dark ? 'bg-slate-950 text-slate-100' : 'bg-slate-100 text-slate-800';
            return dark ? 'bg-slate-950 text-slate-100' : 'bg-white text-slate-900';
        },
        fullscreenSurfaceClass() {
            const dark = this.combinedPreferences.theme === 'dark';
            return {
                glassmorphism: dark ? 'rounded-2xl bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-2xl border border-white/20 shadow-[0_8px_32px_rgba(0,0,0,0.5)]' : 'rounded-2xl bg-gradient-to-br from-white/60 to-white/20 backdrop-blur-2xl border border-white shadow-[0_8px_32px_rgba(31,38,135,0.15)]',
                neumorphism: dark ? 'rounded-2xl bg-slate-800 shadow-[8px_8px_20px_#0f172a,-8px_-8px_20px_#334155]' : 'rounded-2xl bg-slate-200 shadow-[12px_12px_28px_#b8c0cc,-12px_-12px_28px_#ffffff]',
                material_elevated: dark ? 'rounded-2xl tpuirf-palette-deep shadow-2xl' : 'rounded-2xl tpuirf-palette-bg shadow-2xl',
                high_contrast_brutalist: dark ? 'bg-slate-800 text-slate-100 border-4 border-black shadow-[9px_9px_0_#000]' : 'bg-stone-100 text-slate-900 border-4 border-slate-900 shadow-[9px_9px_0_#94a3b8]',
                minimal_flat: dark ? 'rounded-xl tpuirf-palette-deep border-2 border-slate-700' : 'rounded-xl tpuirf-palette-bg border-2 border-slate-300',
            }[this.fullscreenStyle] || (dark ? 'rounded-2xl bg-slate-800' : 'rounded-2xl bg-white');
        },
        fullscreenNavigationClass() {
            const dark = this.combinedPreferences.theme === 'dark';
            return {
                glassmorphism: dark ? 'bg-white/10 text-white backdrop-blur-2xl border border-white/20 shadow-2xl' : 'bg-white/35 text-slate-900 backdrop-blur-2xl border border-white/80 shadow-2xl',
                neumorphism: dark ? 'bg-slate-800 text-slate-100 shadow-[8px_8px_20px_#0f172a,-8px_-8px_20px_#334155]' : 'bg-slate-200 text-slate-900 shadow-[8px_8px_20px_#b8c0cc,-8px_-8px_20px_#ffffff]',
                material_elevated: dark ? 'tpuirf-palette-deep text-white shadow-xl' : 'tpuirf-palette-bg text-slate-900 shadow-xl',
                high_contrast_brutalist: dark ? 'bg-slate-800 text-slate-100 border-4 border-black shadow-[7px_7px_0_#000]' : 'bg-stone-100 text-slate-900 border-4 border-slate-900 shadow-[7px_7px_0_#94a3b8]',
                minimal_flat: dark ? 'tpuirf-palette-deep text-white border-2 border-slate-700' : 'tpuirf-palette-bg text-slate-900 border-2 border-slate-300',
            }[this.fullscreenStyle] || 'tpuirf-palette-primary text-white';
        },
        fullscreenNavItemClass(item) {
            const active = this.demoActiveNav === item;
            const dark = this.combinedPreferences.theme === 'dark';
            if (this.fullscreenStyle === 'glassmorphism') return active ? 'rounded-xl bg-white/30 backdrop-blur-xl border border-white/50 shadow-lg' : 'rounded-xl border border-transparent hover:bg-white/10';
            if (this.fullscreenStyle === 'neumorphism') return dark ? `rounded-xl bg-slate-800 transition-shadow duration-300 ${active ? 'text-indigo-400 shadow-[inset_4px_4px_8px_#0f172a,inset_-4px_-4px_8px_#334155]' : 'shadow-[4px_4px_8px_#0f172a,-4px_-4px_8px_#334155] hover:shadow-[inset_3px_3px_6px_#0f172a,inset_-3px_-3px_6px_#334155]'}` : `rounded-xl bg-slate-200 transition-shadow duration-300 ${active ? 'text-indigo-700 shadow-[inset_4px_4px_8px_#b8c0cc,inset_-4px_-4px_8px_#ffffff]' : 'shadow-[4px_4px_8px_#b8c0cc,-4px_-4px_8px_#ffffff] hover:shadow-[inset_3px_3px_6px_#b8c0cc,inset_-3px_-3px_6px_#ffffff]'}`;
            if (this.fullscreenStyle === 'high_contrast_brutalist') return active ? 'bg-indigo-400 text-slate-900 border-4 border-black shadow-[4px_4px_0_#000]' : (dark ? 'bg-slate-700 text-slate-100 border-4 border-black hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0_#000] hover:bg-slate-600 transition-all' : 'bg-stone-100 text-slate-900 border-4 border-slate-900 hover:bg-emerald-100');
            if (this.fullscreenStyle === 'minimal_flat') return active ? 'rounded-lg tpuirf-palette-primary text-white' : 'rounded-lg tpuirf-palette-soft';
            return active ? 'rounded-xl tpuirf-palette-primary text-white shadow-md' : 'rounded-xl tpuirf-palette-soft';
        },
        fullscreenInputClass() {
            const dark = this.combinedPreferences.theme === 'dark';
            return {
                glassmorphism: dark ? 'rounded-xl bg-white/10 text-white placeholder-white/60 backdrop-blur-xl border border-white/30 shadow-lg' : 'rounded-xl bg-white/35 text-slate-900 placeholder-slate-600 backdrop-blur-xl border border-white/80 shadow-lg',
                neumorphism: dark ? 'rounded-xl bg-slate-800 text-white border-0 shadow-[inset_5px_5px_10px_#0f172a,inset_-5px_-5px_10px_#334155]' : 'rounded-xl bg-slate-200 text-slate-900 border-0 shadow-[inset_5px_5px_10px_#b8c0cc,inset_-5px_-5px_10px_#ffffff]',
                material_elevated: dark ? 'rounded-xl bg-slate-800 text-white border border-slate-700 shadow-lg' : 'rounded-xl bg-white text-slate-900 border-0 shadow-lg',
                high_contrast_brutalist: dark ? 'bg-slate-800 text-slate-100 border-4 border-black shadow-[5px_5px_0_#000] placeholder-slate-400' : 'bg-stone-50 text-slate-900 border-4 border-slate-900 shadow-[5px_5px_0_#94a3b8] placeholder-slate-500',
                minimal_flat: dark ? 'rounded-lg bg-slate-900 text-white border-2 border-slate-700' : 'rounded-lg bg-white text-slate-900 border-2 border-slate-300',
            }[this.fullscreenStyle] || 'rounded-xl bg-white text-slate-900';
        },
        fullscreenButtonClass(primary = true) {
            const dark = this.combinedPreferences.theme === 'dark';
            if (this.fullscreenStyle === 'glassmorphism') return primary ? 'rounded-xl bg-white/30 backdrop-blur-xl border border-white/60 shadow-lg hover:bg-white/40' : 'rounded-xl bg-white/10 backdrop-blur-xl border border-white/30 hover:bg-white/20';
            if (this.fullscreenStyle === 'neumorphism') return dark ? `rounded-xl bg-slate-800 text-indigo-400 ${primary ? 'shadow-[6px_6px_12px_#0f172a,-6px_-6px_12px_#334155] hover:shadow-[4px_4px_8px_#0f172a,-4px_-4px_8px_#334155]' : 'shadow-[inset_4px_4px_8px_#0f172a,inset_-4px_-4px_8px_#334155] hover:shadow-[inset_2px_2px_4px_#0f172a,inset_-2px_-2px_4px_#334155]'}` : `rounded-xl bg-slate-200 text-indigo-700 ${primary ? 'shadow-[6px_6px_12px_#b8c0cc,-6px_-6px_12px_#ffffff] hover:shadow-[4px_4px_8px_#b8c0cc,-4px_-4px_8px_#ffffff]' : 'shadow-[inset_4px_4px_8px_#b8c0cc,inset_-4px_-4px_8px_#ffffff] hover:shadow-[inset_2px_2px_4px_#b8c0cc,inset_-2px_-2px_4px_#ffffff]'}`;
            if (this.fullscreenStyle === 'high_contrast_brutalist') return primary ? 'bg-indigo-400 text-slate-900 border-4 border-black shadow-[5px_5px_0_#000] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[3px_3px_0_#000] hover:bg-indigo-500 transition-all' : (dark ? 'bg-slate-700 text-slate-100 border-4 border-black shadow-[5px_5px_0_#000] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[3px_3px_0_#000] hover:bg-slate-600 transition-all' : 'bg-stone-100 text-slate-900 border-4 border-slate-900 shadow-[5px_5px_0_#94a3b8] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[3px_3px_0_#94a3b8] hover:bg-stone-200 transition-all');
            if (this.fullscreenStyle === 'minimal_flat') return primary ? 'rounded-lg tpuirf-palette-primary text-white' : 'rounded-lg border-2 border-current tpuirf-palette-soft';
            return primary ? 'rounded-xl tpuirf-palette-primary text-white shadow-lg' : 'rounded-xl border border-current/20 tpuirf-palette-soft';
        },
        fullscreenIconButtonClass() { return this.fullscreenButtonClass(false); },
        fullscreenTextActionClass() { return this.fullscreenStyle === 'high_contrast_brutalist' ? 'font-black underline decoration-4 hover:opacity-70' : 'tpuirf-palette-primary-text'; },
        fullscreenListItemClass() {
            if (this.fullscreenStyle === 'glassmorphism') return 'rounded-xl bg-white/10 backdrop-blur-xl border border-white/30 hover:bg-white/20';
            if (this.fullscreenStyle === 'neumorphism') return this.combinedPreferences.theme === 'dark' ? 'rounded-xl bg-slate-800 shadow-[inset_3px_3px_7px_#0f172a,inset_-3px_-3px_7px_#334155]' : 'rounded-xl bg-slate-200 shadow-[inset_3px_3px_7px_#b8c0cc,inset_-3px_-3px_7px_#ffffff]';
            const dark = this.combinedPreferences.theme === 'dark';
            if (this.fullscreenStyle === 'high_contrast_brutalist') return dark ? 'bg-slate-800 text-slate-100 border-4 border-black hover:bg-slate-700 transition-colors' : 'tpuirf-palette-soft text-slate-900 border-4 border-slate-900';
            return 'rounded-xl border border-current/10 tpuirf-palette-soft';
        },
        fullscreenIconBadgeClass() {
            if (this.fullscreenStyle === 'glassmorphism') return 'rounded-full bg-white/20 backdrop-blur-xl border border-white/40';
            if (this.fullscreenStyle === 'neumorphism') return this.combinedPreferences.theme === 'dark' ? 'rounded-full bg-slate-800 shadow-[4px_4px_8px_#0f172a,-4px_-4px_8px_#334155]' : 'rounded-full bg-slate-200 shadow-[4px_4px_8px_#b8c0cc,-4px_-4px_8px_#ffffff]';
            if (this.fullscreenStyle === 'high_contrast_brutalist') return 'tpuirf-palette-accent text-slate-900 border-4 border-black shadow-[2px_2px_0_#000]';
            return 'rounded-full tpuirf-palette-primary text-white';
        },
        fullscreenToastClass() {
            if (this.fullscreenStyle === 'glassmorphism') return 'rounded-xl bg-white/20 backdrop-blur-2xl border border-white/50 shadow-2xl';
            if (this.fullscreenStyle === 'neumorphism') return this.combinedPreferences.theme === 'dark' ? 'rounded-xl bg-slate-800 text-white shadow-[7px_7px_15px_#0f172a,-7px_-7px_15px_#334155]' : 'rounded-xl bg-slate-200 text-slate-900 shadow-[7px_7px_15px_#b8c0cc,-7px_-7px_15px_#ffffff]';
            const dark = this.combinedPreferences.theme === 'dark';
            if (this.fullscreenStyle === 'high_contrast_brutalist') return dark ? 'bg-slate-800 text-slate-100 border-4 border-black shadow-[6px_6px_0_#000]' : 'tpuirf-palette-soft text-slate-900 border-4 border-slate-900 shadow-[6px_6px_0_#64748b]';
            return 'rounded-xl tpuirf-palette-deep text-white shadow-2xl';
        },
        fullscreenModalOverlayClass() { return this.fullscreenStyle === 'glassmorphism' ? 'bg-slate-950/40' : this.fullscreenStyle === 'high_contrast_brutalist' ? 'bg-slate-800/55' : 'bg-slate-950/60'; },
    };
}
</script>
@endpush
