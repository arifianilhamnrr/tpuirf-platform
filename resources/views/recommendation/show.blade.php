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
        $recommendedConfig = $recommendation?->recommendedConfig ?? [];
        $usingFallback = str_starts_with($recommendation?->modelVersion ?? '', 'fallback-');
    @endphp
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Rekomendasi Antarmuka Untuk Anda</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Tampilan ini dirancang khusus menyesuaikan profil, kebiasaan, dan cara Anda berinteraksi selama sesi uji coba.</p>
        </div>

        <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-8 border border-white/20 dark:border-gray-700/30 shadow-xl">
            @if($recommendation && !empty($recommendedConfig))
                <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                    <div class="w-24 h-24 shrink-0 rounded-3xl bg-gradient-to-br from-indigo-500 to-cyan-400 flex items-center justify-center shadow-2xl shadow-indigo-500/20">
                        <span class="text-3xl font-bold text-white">UI</span>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Konfigurasi Pilihan Sistem (AI)</h2>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $usingFallback ? 'bg-amber-500/15 text-amber-700 dark:text-amber-300' : 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' }}">{{ $usingFallback ? 'Ringkasan sementara' : 'Prediksi AI' }}</span>
                        </div>
                        <p class="mt-1 text-gray-500 dark:text-gray-400">{{ $usingFallback ? 'Rekomendasi AI belum tersedia; sistem menampilkan susunan sementara berbasis profil tanpa memakai pilihan manual Anda.' : 'XGBoost menyusun antarmuka berikut dari profil dan pola interaksi Anda, dengan tingkat kecocokan ' . number_format($recommendation->confidenceScore * 100, 1) . '%:' }}</p>
                        <div class="mt-3 flex flex-wrap gap-2 text-sm">
                            @if(!empty($recommendedConfig['design_style']))
                                <span class="rounded-full bg-indigo-500/10 px-3 py-1 font-medium text-indigo-700 dark:text-indigo-300">{{ $preferenceValue($recommendedConfig['design_style']) }}</span>
                            @endif
                            @if(!empty($recommendedConfig['color_palette']))
                                <span class="rounded-full bg-cyan-500/10 px-3 py-1 font-medium text-cyan-700 dark:text-cyan-300">Palet {{ $preferenceValue($recommendedConfig['color_palette']) }}</span>
                            @endif
                            <span class="rounded-full bg-gray-500/10 px-3 py-1 font-medium text-gray-600 dark:text-gray-300">Confidence {{ number_format($recommendation->confidenceScore * 100, 1) }}%</span>
                        </div>
                    </div>
                </div>
                <div class="mt-8 grid gap-3 sm:grid-cols-2">
                    @foreach([
                        'Tema' => $recommendedConfig['theme'] ?? null,
                        'Palet Warna' => $recommendedConfig['color_palette'] ?? null,
                        'Tipografi' => $recommendedConfig['font_family'] ?? null,
                        'Ukuran Font' => $recommendedConfig['font_size'] ?? null,
                        'Kepadatan Layout' => $recommendedConfig['layout_density'] ?? null,
                        'Navigasi' => $recommendedConfig['navigation_position'] ?? null,
                        'Gaya Ikon' => $recommendedConfig['icon_style'] ?? null,
                        'Gaya Desain' => $recommendedConfig['design_style'] ?? null,
                    ] as $label => $value)
                        <div class="rounded-2xl bg-gray-50 dark:bg-gray-900/60 p-4">
                            <p class="text-xs uppercase tracking-wide text-gray-500">{{ $label }}</p>
                            <p class="mt-1 font-semibold text-gray-800 dark:text-white">{{ $preferenceValue($value) }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Belum ada rekomendasi</h2>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Selesaikan eksperimen agar sistem dapat membaca profil dan pola interaksi Anda.</p>
                </div>
            @endif
        </div>

        @if($preferenceExperiment && !empty($finalPreferences))
            <div class="rounded-3xl border border-white/20 bg-white/60 p-6 shadow-lg backdrop-blur-xl dark:border-gray-700/30 dark:bg-gray-800/40">
                <h2 class="font-bold text-gray-800 dark:text-white">Pilihan akhir Anda sebagai data penelitian</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilihan akhir yang Anda tetapkan ini digunakan sebagai data pembelajaran untuk melatih model menjadi lebih akurat, dan disimpan terpisah dari hasil rekomendasi di atas.</p>
                <div class="mt-4 flex flex-wrap gap-2 text-sm">
                    @foreach(($finalPreferences['design_styles'] ?? []) as $style)
                        <span class="rounded-full bg-gray-500/10 px-3 py-1 text-gray-700 dark:text-gray-300">{{ $preferenceValue($style) }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @if(!empty($explanations))
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-8 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Mengapa Tampilan Ini Direkomendasikan? (Analisis Faktor)</h2>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Angka di bawah ini menunjukkan seberapa besar pengaruh tiap karakteristik Anda terhadap keputusan sistem (bukan hubungan sebab-akibat mutlak). Faktor dengan pengaruh paling kuat ditampilkan paling atas.</p>
                <div class="space-y-4">
                    @foreach($explanations as $explanation)
                        <div class="p-4 rounded-2xl bg-gradient-to-r from-indigo-500/5 to-cyan-500/5 border border-indigo-500/10">
                            <div class="flex items-center justify-between mb-2">
                                <div><p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">{{ $explanation['target_label'] ?? 'Antarmuka' }} · {{ $explanation['recommended_value_label'] ?? '-' }}</p><h3 class="font-semibold text-gray-800 dark:text-white">{{ $explanation['feature'] }}</h3></div>
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

        @if($recommendation && !$usingFallback)
            <section id="evaluasi-xai" class="scroll-mt-24 backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 sm:p-8 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <div class="max-w-3xl">
                    <p class="text-xs font-semibold uppercase tracking-[.18em] text-indigo-600 dark:text-indigo-400">Evaluasi Akhir</p>
                    <h2 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white">Evaluasi Rekomendasi dan Penjelasan AI</h2>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Setelah melihat rekomendasi dan analisis faktor di atas, berikan penilaian Anda pada setiap pernyataan berikut. Pilih satu jawaban dari 1 (Sangat Tidak Setuju) sampai 5 (Sangat Setuju).</p>
                    <p id="xai-scale-guide" class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">Jawaban ini digunakan untuk mengevaluasi kualitas rekomendasi dan penjelasan AI. Jawaban tidak mengubah rekomendasi yang sudah Anda terima dan tidak digunakan sebagai input prediksi untuk sesi ini.</p>
                </div>

                @if(session('success'))
                    <div role="status" class="mt-5 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-300">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('respondent.recommendation.xai-evaluation.store') }}" class="mt-7 space-y-6">
                    @csrf
                    @foreach($xaiItems as $key => $item)
                        <fieldset class="rounded-2xl border border-gray-200/80 bg-white/60 p-4 sm:p-5 dark:border-gray-700 dark:bg-gray-900/30" aria-describedby="xai-scale-guide {{ $errors->has($key) ? $key.'-error' : '' }}">
                            <legend class="w-full px-1">
                                <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">{{ $loop->iteration }}. {{ $item['construct'] }}</span>
                                <span class="mt-2 block text-sm font-semibold leading-6 text-gray-800 dark:text-white">{{ $item['statement'] }}</span>
                            </legend>
                            <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-5">
                                @foreach($xaiScale as $score => $label)
                                    @php $selected = (int) old($key, $xaiEvaluation?->{$key}) === (int) $score; @endphp
                                    <label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-xl border px-3 py-2 text-sm transition {{ $selected ? 'border-indigo-500 bg-indigo-500/10 text-indigo-700 dark:text-indigo-300' : 'border-gray-200 bg-white/70 text-gray-600 hover:border-indigo-300 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-300' }}">
                                        <input type="radio" name="{{ $key }}" value="{{ $score }}" class="h-5 w-5 shrink-0 text-indigo-600 focus:ring-indigo-500" {{ $selected ? 'checked' : '' }} required>
                                        <span><strong>{{ $score }}</strong><span class="block text-xs leading-4">{{ $label }}</span></span>
                                    </label>
                                @endforeach
                            </div>
                            @error($key)<p id="{{ $key }}-error" class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
                        </fieldset>
                    @endforeach
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $xaiEvaluation ? 'Jawaban sebelumnya akan diperbarui tanpa membuat data duplikat.' : 'Semua pernyataan wajib dijawab.' }}</p>
                        <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 px-7 py-3 font-semibold text-white shadow-lg shadow-indigo-500/20 transition hover:from-indigo-700 hover:to-cyan-600">
                            {{ $xaiEvaluation ? 'Perbarui Evaluasi' : 'Kirim Evaluasi' }}
                        </button>
                    </div>
                </form>
            </section>
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
