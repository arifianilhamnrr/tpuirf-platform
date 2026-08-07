@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Dashboard Administrator</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Pengguna</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $stats['total_respondents'] ?? 0 }}</p>
            </div>
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Eksperimen</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $stats['total_experiments'] ?? 0 }}</p>
            </div>
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Rata-rata Skor Kepercayaan</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ number_format($stats['avg_trust_score'] ?? 0, 1) }}</p>
            </div>
            <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Rata-rata Transparansi</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ number_format($stats['avg_transparency_score'] ?? 0, 1) }}</p>
            </div>
        </div>

        <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-8 border border-white/20 dark:border-gray-700/30 shadow-xl">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6">Pengelolaan Sistem</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="#" class="p-4 rounded-2xl bg-gradient-to-br from-indigo-500/10 to-purple-500/10 border border-indigo-500/20 hover:shadow-lg transition-all duration-200">
                    <h3 class="font-semibold text-gray-800 dark:text-white">Kelola Peneliti</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tambah atau hapus akun peneliti</p>
                </a>
                <a href="#" class="p-4 rounded-2xl bg-gradient-to-br from-cyan-500/10 to-teal-500/10 border border-cyan-500/20 hover:shadow-lg transition-all duration-200">
                    <h3 class="font-semibold text-gray-800 dark:text-white">Kelola Eksperimen</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Lihat seluruh eksperimen</p>
                </a>
                <a href="#" class="p-4 rounded-2xl bg-gradient-to-br from-amber-500/10 to-orange-500/10 border border-amber-500/20 hover:shadow-lg transition-all duration-200">
                    <h3 class="font-semibold text-gray-800 dark:text-white">Konfigurasi</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pengaturan sistem</p>
                </a>
            </div>
        </div>
    </div>
@endsection
