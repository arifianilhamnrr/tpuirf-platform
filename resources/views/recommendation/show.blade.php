@extends('layouts.app')

@section('title', 'Rekomendasi Anda')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Rekomendasi Personal Anda</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Berdasarkan profil dan perilaku interaksi Anda</p>
        </div>

        <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-8 border border-white/20 dark:border-gray-700/30 shadow-xl text-center">
            <div class="w-24 h-24 mx-auto mb-4 rounded-3xl bg-gradient-to-br from-indigo-500 to-cyan-400 flex items-center justify-center shadow-2xl shadow-indigo-500/20">
                <span class="text-4xl font-bold text-white">{{ $recommendation ? $recommendation->recommendedUi : '--' }}</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Versi UI {{ $recommendation ? $recommendation->recommendedUi : 'Belum tersedia' }}</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Antarmuka yang Direkomendasikan</p>
            @if($recommendation)
                <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-gradient-to-r from-green-500/10 to-emerald-500/10 border border-green-500/20 text-green-700 dark:text-green-300 text-sm">
                    <span>Tingkat keyakinan: {{ number_format($recommendation->confidenceScore * 100, 1) }}%</span>
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
