@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $researchStatus = $preferenceExperiment['status'] ?? null;
        $researchActive = in_array($researchStatus, ['in_progress', 'evaluation'], true);
        $researchCompleted = $researchStatus === 'completed';
        $hasActiveExperiment = $researchActive || (bool) $activeExperiment;
    @endphp
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Selamat datang, {{ $user->name }}</h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ $user->email }}</p>
            </div>
        </div>

        @if(!isset($profileComplete) || !$profileComplete)
            <div class="backdrop-blur-xl bg-gradient-to-r from-indigo-500/10 to-cyan-500/10 dark:from-indigo-500/20 dark:to-cyan-500/20 rounded-3xl p-6 border border-indigo-500/20 dark:border-indigo-500/30 shadow-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 flex items-center justify-center text-white shadow-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 dark:text-white">Lengkapi Profil Anda</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Bantu kami memahami karakteristik Anda</p>
                        </div>
                    </div>
                    <a href="{{ route('respondent.profile.show') }}" class="px-6 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 text-white text-sm font-semibold hover:from-indigo-700 hover:to-cyan-600 transition-all duration-200 shadow-lg shadow-indigo-500/25">Lengkapi Sekarang</a>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl hover:shadow-2xl transition-all duration-300">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="font-semibold text-gray-800 dark:text-white">Eksperimen</h3>
                </div>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $hasActiveExperiment ? '1 Aktif' : '0' }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $researchCompleted ? 'Penelitian preferensi selesai' : ($hasActiveExperiment ? 'Eksperimen sedang berlangsung' : 'Tidak ada eksperimen aktif') }}</p>
            </div>

            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl hover:shadow-2xl transition-all duration-300">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-teal-500 flex items-center justify-center text-white shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="font-semibold text-gray-800 dark:text-white">Rekomendasi</h3>
                </div>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $recommendation ? $recommendation->recommendedUi : '--' }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $recommendation ? 'Tingkat keyakinan: '.($recommendation->confidenceScore * 100).'%' : 'Belum dibuat' }}</p>
            </div>

            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl hover:shadow-2xl transition-all duration-300">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="font-semibold text-gray-800 dark:text-white">Skor Kepercayaan</h3>
                </div>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">--</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Selesaikan evaluasi untuk melihatnya</p>
            </div>
        </div>

        @if($profileComplete)
        @if($researchCompleted)
            <div class="backdrop-blur-xl bg-gradient-to-r from-emerald-500/10 to-cyan-500/10 dark:from-emerald-500/20 dark:to-cyan-500/20 rounded-3xl p-8 border border-emerald-500/20 shadow-xl">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
                    <div>
                        <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">Penelitian preferensi selesai</p>
                        <h2 class="mt-1 text-xl font-bold text-gray-800 dark:text-white">Seluruh {{ count(config('experiment.tasks')) }} tugas dan evaluasi sudah tersimpan</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Anda tetap dapat membuka ringkasan pilihan akhir.</p>
                    </div>
                    <a href="{{ route('respondent.preference-experiment.complete.page', $preferenceExperiment['id']) }}" class="shrink-0 px-6 py-3 rounded-2xl bg-emerald-600 text-white text-sm font-semibold shadow-lg">Lihat Ringkasan</a>
                </div>
            </div>
        @elseif($researchActive)
            @php
                $isEvaluation = $researchStatus === 'evaluation';
                $continueUrl = $isEvaluation
                    ? route('respondent.preference-experiment.evaluation.page', $preferenceExperiment['id'])
                    : route('respondent.preference-experiment.tasks.page', [
                        'experimentId' => $preferenceExperiment['id'],
                        'taskKey' => $preferenceExperiment['current_task'],
                    ]);
                $progress = $isEvaluation ? 100 : (($preferenceExperiment['current_step'] / 6) * 100);
            @endphp
            <div class="backdrop-blur-xl bg-gradient-to-r from-indigo-500/10 to-cyan-500/10 dark:from-indigo-500/20 dark:to-cyan-500/20 rounded-3xl p-8 border border-indigo-500/20 dark:border-indigo-500/30 shadow-xl">
                <div class="flex items-center justify-between gap-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Lanjutkan Penelitian Preferensi</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ $isEvaluation ? 'Evaluasi akhir' : 'Tugas '.$preferenceExperiment['current_step'].' dari '.count(config('experiment.tasks')) }}</p>
                    </div>
                    <a href="{{ $continueUrl }}" class="shrink-0 px-6 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 text-white text-sm font-semibold shadow-lg">Lanjutkan</a>
                </div>
                <div class="mt-4 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-indigo-500 to-cyan-400 rounded-full" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        @elseif(!$activeExperiment)
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-8 border border-white/20 dark:border-gray-700/30 shadow-xl text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-3xl bg-gradient-to-br from-indigo-500 to-cyan-400 flex items-center justify-center shadow-2xl shadow-indigo-500/20">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Siap Memulai?</h2>
                <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">Anda akan menyelesaikan {{ count(config('experiment.tasks')) }} tugas preferensi UI, mencoba pratinjau langsung yang responsif, lalu memberi evaluasi akhir.</p>
                <a href="{{ route('respondent.preference-experiment.introduction') }}" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 text-white font-semibold hover:from-indigo-700 hover:to-cyan-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200 shadow-lg shadow-indigo-500/25 transform hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Mulai Eksperimen
                </a>
            </div>
        @else
            <div class="backdrop-blur-xl bg-gradient-to-r from-indigo-500/10 to-cyan-500/10 dark:from-indigo-500/20 dark:to-cyan-500/20 rounded-3xl p-8 border border-indigo-500/20 dark:border-indigo-500/30 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Lanjutkan Eksperimen</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Langkah {{ $activeExperiment->currentStep }} dari 4</p>
                    </div>
                    <a href="{{ route('respondent.experiment.run', ['step' => $activeExperiment->currentStep]) }}" class="px-6 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 text-white text-sm font-semibold hover:from-indigo-700 hover:to-cyan-600 transition-all duration-200 shadow-lg shadow-indigo-500/25">Lanjutkan</a>
                </div>
                <div class="mt-4 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-indigo-500 to-cyan-400 rounded-full transition-all duration-500" style="width: {{ ($activeExperiment->currentStep / 4) * 100 }}%"></div>
                </div>
            </div>
        @endif
        @endif
    </div>
@endsection
