@extends('layouts.app')

@section('title', 'Dashboard Peneliti')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Dashboard Peneliti</h1>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Responden</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $stats['total_respondents'] ?? 0 }}</p>
            </div>
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Eksperimen</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $stats['total_experiments'] ?? 0 }}</p>
            </div>
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Selesai</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $stats['completed_experiments'] ?? 0 }}</p>
            </div>
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Interaksi</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $stats['total_interactions'] ?? 0 }}</p>
            </div>
        </div>

        <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-8 border border-white/20 dark:border-gray-700/30 shadow-xl">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6">Aksi Cepat</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('analytics.index') }}" class="p-4 rounded-2xl bg-gradient-to-br from-indigo-500/10 to-purple-500/10 border border-indigo-500/20 hover:shadow-lg transition-all duration-200">
                    <h3 class="font-semibold text-gray-800 dark:text-white">Lihat Analitik</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Lihat statistik penelitian secara rinci</p>
                </a>
                <a href="{{ route('export.csv', ['type' => 'raw-events']) }}" class="p-4 rounded-2xl bg-gradient-to-br from-cyan-500/10 to-teal-500/10 border border-cyan-500/20 hover:shadow-lg transition-all duration-200">
                    <h3 class="font-semibold text-gray-800 dark:text-white">Ekspor Event Mentah</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">CSV semua klik, gulir, waktu, dan transisi</p>
                </a>
                <a href="{{ route('export.csv', ['type' => 'preferences']) }}" class="p-4 rounded-2xl bg-gradient-to-br from-green-500/10 to-emerald-500/10 border border-green-500/20 hover:shadow-lg transition-all duration-200">
                    <h3 class="font-semibold text-gray-800 dark:text-white">Ekspor Preferensi</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">CSV konfigurasi preferensi akhir partisipan</p>
                </a>
            </div>
        </div>
    </div>
@endsection
