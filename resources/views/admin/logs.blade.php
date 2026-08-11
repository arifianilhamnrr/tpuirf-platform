@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
<div class="space-y-6">
    <div><p class="text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Administrasi</p><h1 class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">Audit Log</h1><p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Jejak aksi administratif lengkap dengan nilai sebelum dan sesudah perubahan.</p></div>
    @include('admin._tabs')
    <section class="rounded-3xl border border-white/20 bg-white/70 p-6 shadow-xl backdrop-blur-xl dark:border-gray-700/30 dark:bg-gray-800/50">
        <form class="grid gap-3 sm:grid-cols-[220px_220px_auto]"><select name="action" class="rounded-xl border-gray-200 bg-white/70 text-sm dark:border-gray-600 dark:bg-gray-900/60 dark:text-white"><option value="">Semua aksi</option>@foreach($actions as $action)<option value="{{ $action }}" @selected(request('action') === $action)>{{ str($action)->replace('_', ' ')->title() }}</option>@endforeach</select><select name="entity" class="rounded-xl border-gray-200 bg-white/70 text-sm dark:border-gray-600 dark:bg-gray-900/60 dark:text-white"><option value="">Semua entitas</option>@foreach($entities as $entity)<option value="{{ $entity }}" @selected(request('entity') === $entity)>{{ $entity }}</option>@endforeach</select><button class="rounded-xl bg-indigo-600 px-5 text-sm font-semibold text-white">Terapkan</button></form>
        <div class="mt-5 space-y-3">
            @forelse($logs as $log)
                <article class="rounded-2xl border border-gray-100 bg-white/60 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"><div><div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-indigo-500/10 px-2.5 py-1 text-xs font-semibold text-indigo-700 dark:text-indigo-300">{{ str($log->action)->replace('_', ' ')->title() }}</span><span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $log->entity_type }}</span></div><p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Oleh {{ $log->user?->name ?? 'Sistem' }} · IP {{ $log->ip_address ?? '-' }}</p></div><time class="text-xs text-gray-500">{{ $log->timestamp?->format('d M Y H:i:s') }}</time></div>
                    @if($log->old_values || $log->new_values)
                        <details class="mt-3"><summary class="cursor-pointer text-xs font-semibold text-indigo-600 dark:text-indigo-400">Lihat perubahan</summary><div class="mt-2 grid gap-3 md:grid-cols-2"><div><p class="mb-1 text-xs text-gray-500">Sebelum</p><pre class="overflow-x-auto rounded-xl bg-gray-950 p-3 text-[11px] text-red-200">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></div><div><p class="mb-1 text-xs text-gray-500">Sesudah</p><pre class="overflow-x-auto rounded-xl bg-gray-950 p-3 text-[11px] text-emerald-200">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></div></div></details>
                    @endif
                </article>
            @empty
                <p class="py-10 text-center text-gray-500">Belum ada aksi administratif tercatat.</p>
            @endforelse
        </div><div class="mt-5">{{ $logs->links() }}</div>
    </section>
</div>
@endsection
