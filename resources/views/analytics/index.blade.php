@extends('layouts.app')

@section('title', 'Dashboard Analitik')

@section('content')
    @php
        $taskLabels = [
            'theme' => 'Tema',
            'typography' => 'Tipografi',
            'layout_density' => 'Kepadatan Tata Letak',
            'navigation_position' => 'Posisi Navigasi',
            'icon_style' => 'Gaya Ikon',
            'design_style' => 'Gaya Desain',
            'global' => 'Umum',
        ];
    @endphp
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Dashboard Analitik</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Skor Kepercayaan</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ number_format($trustScore, 1) }}</p>
            </div>
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Transparansi</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ number_format($transparencyScore, 1) }}</p>
            </div>
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Akurasi</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ number_format($accuracy * 100, 1) }}%</p>
            </div>
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Responden</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $stats['total_respondents'] ?? 0 }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Durasi Aktif per Tugas</h2>
                <div class="space-y-3">
                    @forelse($taskDurationStats as $duration)
                        <div class="flex items-center justify-between gap-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 p-3">
                            <div><p class="font-medium text-gray-800 dark:text-white">{{ $taskLabels[$duration['task_key']] ?? $duration['task_key'] }}</p><p class="text-xs text-gray-500">{{ $duration['sample_count'] }} sampel</p></div>
                            <p class="font-semibold text-indigo-600 dark:text-indigo-400">{{ number_format(($duration['avg_active_duration_ms'] ?? 0) / 1000, 1) }} detik</p>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada tugas yang selesai.</p>
                    @endforelse
                </div>
            </div>
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Komponen Paling Sering Diklik</h2>
                <div class="space-y-3">
                    @forelse(array_slice($clickDistribution, 0, 10) as $click)
                        <div class="flex items-center justify-between gap-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 p-3">
                            <div class="min-w-0"><p class="truncate font-medium text-gray-800 dark:text-white">{{ $click['component_id'] }}</p><p class="text-xs text-gray-500">{{ $taskLabels[$click['task_key'] ?? 'global'] ?? ($click['task_key'] ?? 'Umum') }}</p></div>
                            <span class="shrink-0 rounded-full bg-cyan-500/10 px-3 py-1 text-sm font-semibold text-cyan-700 dark:text-cyan-300">{{ $click['event_count'] }} klik</span>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada event klik.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl">
            <div class="flex flex-col gap-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white">Evaluasi XAI Pascarekomendasi</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $xaiEvaluationStats['total_submitted'] }} dari {{ $xaiEvaluationStats['total_recommendations'] }} rekomendasi telah dinilai.</p>
                    </div>
                    <div class="rounded-2xl bg-indigo-500/10 px-5 py-3 text-center">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Tingkat Penyelesaian</p>
                        <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">{{ number_format($xaiEvaluationStats['completion_rate'], 1) }}%</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach(config('xai_evaluation.items') as $key => $item)
                        <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-900/50">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $item['construct'] }}</p>
                            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($xaiEvaluationStats['means'][$key] ?? 0, 2) }}<span class="text-sm font-normal text-gray-400"> / 5</span></p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div><h2 class="text-lg font-bold text-gray-800 dark:text-white">Ekspor Dataset Penelitian</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Identitas partisipan memakai kode eksperimen, tanpa nama atau tanggal lahir.</p></div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('export.csv', ['type' => 'raw-events']) }}" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold">Event Mentah</a>
                    <a href="{{ route('export.csv', ['type' => 'task-summary']) }}" class="px-4 py-2 rounded-xl bg-cyan-600 text-white text-sm font-semibold">Ringkasan Tugas</a>
                    <a href="{{ route('export.csv', ['type' => 'transitions']) }}" class="px-4 py-2 rounded-xl bg-purple-600 text-white text-sm font-semibold">Transisi</a>
                    <a href="{{ route('export.csv', ['type' => 'preferences']) }}" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold">Preferensi</a>
                    <a href="{{ route('export.csv', ['type' => 'xai-evaluations']) }}" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-semibold">Evaluasi XAI</a>
                </div>
            </div>
        </div>
    </div>
@endsection
