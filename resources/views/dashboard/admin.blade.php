@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    @php
        $modelStatus = $modelStatus ?? [];
        $featureImportance = $featureImportance ?? [];
        $recentPredictions = $recentPredictions ?? collect();
        $stats = $stats ?? [];
    @endphp
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Dashboard Administrator</h1>
        @include('admin._tabs')

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

        <div class="grid gap-6 lg:grid-cols-[1.1fr_1fr]">
            <section class="rounded-3xl border border-white/20 bg-white/70 p-7 shadow-xl backdrop-blur-xl dark:border-gray-700/30 dark:bg-gray-800/50">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.18em] text-indigo-600 dark:text-indigo-400">Model AI Terintegrasi</p>
                        <h2 class="mt-2 text-xl font-bold text-gray-800 dark:text-white">Random Forest + XGBoost + SHAP</h2>
                    </div>
                    <div class="flex items-center gap-2"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ ($modelStatus['model_loaded'] ?? false) ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' : 'bg-amber-500/15 text-amber-700 dark:text-amber-300' }}">{{ ($modelStatus['model_loaded'] ?? false) ? 'Model aktif' : 'Model belum aktif' }}</span><form method="POST" action="{{ route('admin.model.train') }}">@csrf<button type="submit" class="rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white">Latih Model</button></form></div>
                </div>
                <dl class="mt-6 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-900/60"><dt class="text-gray-500">Versi</dt><dd class="mt-1 font-semibold text-gray-800 dark:text-white">{{ $modelStatus['model_version'] ?? '-' }}</dd></div>
                    <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-900/60"><dt class="text-gray-500">Akurasi</dt><dd class="mt-1 font-semibold text-gray-800 dark:text-white">{{ isset($modelStatus['accuracy']) ? number_format($modelStatus['accuracy'] * 100, 1).'%' : '-' }}</dd></div>
                    <div class="col-span-2 rounded-2xl bg-gray-50 p-4 dark:bg-gray-900/60"><dt class="text-gray-500">Algoritma</dt><dd class="mt-1 font-semibold text-gray-800 dark:text-white">{{ collect($modelStatus['models'] ?? [])->map(fn ($model) => str($model)->replace('_', ' ')->title())->join(' · ') ?: '-' }}</dd></div>
                    <div class="col-span-2 rounded-2xl bg-gray-50 p-4 dark:bg-gray-900/60"><dt class="text-gray-500">Target Aktif</dt><dd class="mt-1 font-semibold text-gray-800 dark:text-white">{{ collect($modelStatus['targets'] ?? [])->map(fn ($target) => str($target)->replace('_', ' ')->title())->join(' · ') ?: '-' }}</dd></div>
                </dl>
            </section>

            <section class="rounded-3xl border border-white/20 bg-white/70 p-7 shadow-xl backdrop-blur-xl dark:border-gray-700/30 dark:bg-gray-800/50">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Feature Importance</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kontribusi fitur gabungan dari kedua model.</p>
                <div class="mt-5 space-y-3">
                    @forelse($featureImportance as $feature)
                        <div>
                            <div class="flex justify-between gap-3 text-sm"><span class="font-medium text-gray-700 dark:text-gray-200">{{ str($feature['feature'])->replace('_', ' ')->title() }}</span><span class="text-gray-500">{{ number_format(($feature['importance'] ?? 0) * 100, 1) }}%</span></div>
                            <div class="mt-1 h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700"><div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-cyan-400" style="width: {{ min(($feature['importance'] ?? 0) * 100, 100) }}%"></div></div>
                        </div>
                    @empty
                        <p class="rounded-2xl bg-amber-500/10 p-4 text-sm text-amber-700 dark:text-amber-300">Jalankan dan latih service Python untuk melihat feature importance.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="rounded-3xl border border-white/20 bg-white/70 p-7 shadow-xl backdrop-blur-xl dark:border-gray-700/30 dark:bg-gray-800/50">
            <div class="flex items-end justify-between gap-4">
                <div><h2 class="text-xl font-bold text-gray-800 dark:text-white">Prediksi dan SHAP Terbaru</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Output model tersimpan otomatis setelah eksperimen selesai.</p></div>
                <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">{{ $stats['total_recommendations'] ?? 0 }} prediksi</span>
            </div>
            <div class="mt-5 overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500 dark:border-gray-700"><tr><th class="px-3 py-3">Pengguna</th><th class="px-3 py-3">Rekomendasi</th><th class="px-3 py-3">Confidence</th><th class="px-3 py-3">Model</th><th class="px-3 py-3">Faktor SHAP Utama</th></tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @forelse($recentPredictions as $prediction)
                            <tr><td class="px-3 py-4 font-medium text-gray-800 dark:text-white">{{ $prediction->user?->name ?? '-' }}</td><td class="px-3 py-4">{{ collect($prediction->recommended_config ?? ['design_style' => $prediction->recommended_ui])->map(fn ($value, $key) => str($key)->replace('_', ' ')->title().': '.str($value)->replace('_', ' ')->title())->join(' · ') }}</td><td class="px-3 py-4">{{ number_format($prediction->confidence_score * 100, 1) }}%</td><td class="px-3 py-4">{{ $prediction->model_version ?? '-' }}</td><td class="px-3 py-4 text-gray-500">{{ str($prediction->explanations[0]['feature'] ?? '-')->replace('_', ' ')->title() }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-8 text-center text-gray-500">Belum ada output model tersimpan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-8 border border-white/20 dark:border-gray-700/30 shadow-xl">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6">Pengelolaan Sistem</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('admin.researchers') }}" class="p-4 rounded-2xl bg-gradient-to-br from-indigo-500/10 to-purple-500/10 border border-indigo-500/20 hover:shadow-lg transition-all duration-200">
                    <h3 class="font-semibold text-gray-800 dark:text-white">Kelola Peneliti</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tambah atau hapus akun peneliti</p>
                </a>
                <a href="{{ route('admin.experiments') }}" class="p-4 rounded-2xl bg-gradient-to-br from-cyan-500/10 to-teal-500/10 border border-cyan-500/20 hover:shadow-lg transition-all duration-200">
                    <h3 class="font-semibold text-gray-800 dark:text-white">Kelola Eksperimen</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Lihat seluruh eksperimen</p>
                </a>
                <a href="{{ route('admin.configuration') }}" class="p-4 rounded-2xl bg-gradient-to-br from-amber-500/10 to-orange-500/10 border border-amber-500/20 hover:shadow-lg transition-all duration-200">
                    <h3 class="font-semibold text-gray-800 dark:text-white">Konfigurasi</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pengaturan sistem</p>
                </a>
            </div>
        </div>
    </div>
@endsection
