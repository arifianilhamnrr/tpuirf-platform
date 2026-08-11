@extends('layouts.app')

@section('title', 'Konfigurasi Sistem')

@section('content')
<div class="space-y-6">
    <div><p class="text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Administrasi</p><h1 class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">Konfigurasi Sistem</h1><p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Kelola metadata konfigurasi terstruktur. Nilai wajib berupa objek atau array JSON.</p></div>
    @include('admin._tabs')
    <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
        <section class="h-fit rounded-3xl border border-white/20 bg-white/70 p-6 shadow-xl backdrop-blur-xl dark:border-gray-700/30 dark:bg-gray-800/50">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Tambah Konfigurasi</h2>
            <form method="POST" action="{{ route('admin.configuration.store') }}" class="mt-5 space-y-4">@csrf
                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Key</label><input name="key" value="{{ old('key') }}" placeholder="experiment.example" required class="mt-1 w-full rounded-xl border-gray-200 bg-white/70 font-mono text-sm dark:border-gray-600 dark:bg-gray-900/60 dark:text-white">@error('key')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror</div>
                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Grup</label><input name="group" value="{{ old('group', 'general') }}" required class="mt-1 w-full rounded-xl border-gray-200 bg-white/70 dark:border-gray-600 dark:bg-gray-900/60 dark:text-white"></div>
                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label><textarea name="description" rows="2" class="mt-1 w-full rounded-xl border-gray-200 bg-white/70 dark:border-gray-600 dark:bg-gray-900/60 dark:text-white">{{ old('description') }}</textarea></div>
                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nilai JSON</label><textarea name="value" rows="7" required class="mt-1 w-full rounded-xl border-gray-200 bg-gray-950 font-mono text-xs text-emerald-300">{{ old('value', json_encode(['enabled' => true], JSON_PRETTY_PRINT)) }}</textarea>@error('value')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror</div>
                <button class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white">Tambah Konfigurasi</button>
            </form>
        </section>
        <div class="space-y-4">
            @forelse($configs as $config)
                <section class="rounded-3xl border border-white/20 bg-white/70 p-6 shadow-xl backdrop-blur-xl dark:border-gray-700/30 dark:bg-gray-800/50">
                    <form method="POST" action="{{ route('admin.configuration.update', $config) }}" class="space-y-4">@csrf @method('PUT')
                        <div class="flex items-center justify-between"><span class="rounded-full bg-cyan-500/10 px-3 py-1 text-xs font-semibold text-cyan-700 dark:text-cyan-300">{{ $config->group }}</span><span class="text-xs text-gray-500">{{ $config->updated_at->diffForHumans() }}</span></div>
                        <div class="grid gap-4 sm:grid-cols-2"><div><label class="text-xs font-semibold uppercase text-gray-500">Key</label><input name="key" value="{{ $config->key }}" required class="mt-1 w-full rounded-xl border-gray-200 bg-white/70 font-mono text-sm dark:border-gray-600 dark:bg-gray-900/60 dark:text-white"></div><div><label class="text-xs font-semibold uppercase text-gray-500">Grup</label><input name="group" value="{{ $config->group }}" required class="mt-1 w-full rounded-xl border-gray-200 bg-white/70 dark:border-gray-600 dark:bg-gray-900/60 dark:text-white"></div></div>
                        <div><label class="text-xs font-semibold uppercase text-gray-500">Deskripsi</label><input name="description" value="{{ $config->description }}" class="mt-1 w-full rounded-xl border-gray-200 bg-white/70 dark:border-gray-600 dark:bg-gray-900/60 dark:text-white"></div>
                        <div><label class="text-xs font-semibold uppercase text-gray-500">Nilai JSON</label><textarea name="value" rows="6" required class="mt-1 w-full rounded-xl border-gray-200 bg-gray-950 font-mono text-xs leading-5 text-emerald-300">{{ json_encode($config->value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</textarea></div>
                        <button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white">Simpan Perubahan</button>
                    </form>
                </section>
            @empty
                <div class="rounded-3xl border border-dashed border-gray-300 p-10 text-center text-gray-500 dark:border-gray-700">Belum ada konfigurasi. Tambahkan melalui formulir di samping.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
