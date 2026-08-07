@extends('layouts.app')

@section('title', 'Eksperimen Selesai')

@section('content')
    <div class="max-w-2xl mx-auto text-center">
        <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-12 border border-white/20 dark:border-gray-700/30 shadow-xl">
            <div class="w-24 h-24 mx-auto mb-6 rounded-3xl bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center shadow-2xl shadow-green-500/20">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">Eksperimen Selesai!</h1>
            <p class="text-gray-500 dark:text-gray-400 mb-8">Terima kasih atas partisipasi Anda. Data Anda telah direkam dan akan digunakan untuk meningkatkan sistem rekomendasi.</p>

            <div class="grid grid-cols-3 gap-4 mb-8">
                <div class="p-4 rounded-2xl bg-gradient-to-br from-indigo-500/10 to-purple-500/10 border border-indigo-500/20">
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">4</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Versi UI</p>
                </div>
                <div class="p-4 rounded-2xl bg-gradient-to-br from-cyan-500/10 to-teal-500/10 border border-cyan-500/20">
                    <p class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">100%</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Selesai</p>
                </div>
                <div class="p-4 rounded-2xl bg-gradient-to-br from-green-500/10 to-emerald-500/10 border border-green-500/20">
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">Siap</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Dataset Tersedia</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('respondent.recommendation.show') }}" class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 text-white font-semibold hover:from-indigo-700 hover:to-cyan-600 transition-all duration-200 shadow-lg shadow-indigo-500/25">
                    Lihat Rekomendasi
                </a>
                <a href="{{ route('respondent.dashboard') }}" class="px-8 py-3.5 rounded-2xl backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold hover:bg-white/90 dark:hover:bg-gray-800/70 transition-all duration-200">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
@endsection
