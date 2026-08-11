@extends('layouts.app')

@section('title', 'Template UI')

@section('content')
<div class="space-y-6">
    <div><p class="text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Administrasi</p><h1 class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">Template UI Eksperimen</h1><p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Edit metadata dan konfigurasi JSON versi A–D yang digunakan eksperimen.</p></div>
    @include('admin._tabs')
    <div class="grid gap-6 lg:grid-cols-2">
        @forelse($templates as $template)
            <section class="rounded-3xl border border-white/20 bg-white/70 p-6 shadow-xl backdrop-blur-xl dark:border-gray-700/30 dark:bg-gray-800/50">
                <div class="flex items-start justify-between gap-4"><div class="flex items-center gap-2"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-lg font-bold text-white">{{ $template->version }}</span><div><h2 class="font-bold text-gray-900 dark:text-white">{{ $template->name }}</h2><p class="text-xs text-gray-500">Diperbarui {{ $template->updated_at->diffForHumans() }}</p></div></div><form method="POST" action="{{ route('admin.templates.status', $template) }}">@csrf @method('PATCH')<button class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $template->is_active ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-gray-500/10 text-gray-600 dark:text-gray-300' }}">{{ $template->is_active ? 'Aktif' : 'Nonaktif' }}</button></form></div>
                <form method="POST" action="{{ route('admin.templates.update', $template) }}" class="mt-5 space-y-4">@csrf @method('PUT')
                    <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nama</label><input name="name" value="{{ old('name', $template->name) }}" required class="mt-1 w-full rounded-xl border-gray-200 bg-white/70 dark:border-gray-600 dark:bg-gray-900/60 dark:text-white"></div>
                    <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label><textarea name="description" rows="2" class="mt-1 w-full rounded-xl border-gray-200 bg-white/70 dark:border-gray-600 dark:bg-gray-900/60 dark:text-white">{{ old('description', $template->description) }}</textarea></div>
                    <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Konfigurasi JSON</label><textarea name="config" rows="12" spellcheck="false" required class="mt-1 w-full rounded-xl border-gray-200 bg-gray-950 font-mono text-xs leading-5 text-emerald-300 dark:border-gray-600">{{ old('config', json_encode($template->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) }}</textarea>@error('config')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror</div>
                    <button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white">Simpan Template {{ $template->version }}</button>
                </form>
            </section>
        @empty
            <p class="rounded-2xl bg-amber-500/10 p-5 text-amber-700">Belum ada template UI.</p>
        @endforelse
    </div>
</div>
@endsection
