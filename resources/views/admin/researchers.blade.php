@extends('layouts.app')

@section('title', 'Kelola Peneliti')

@section('content')
<div class="space-y-6">
    <div><p class="text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Administrasi</p><h1 class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">Kelola Peneliti</h1><p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Buat akun peneliti dan atur aksesnya tanpa menghapus riwayat penelitian.</p></div>
    @include('admin._tabs')

    <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
        <section class="h-fit rounded-3xl border border-white/20 bg-white/70 p-6 shadow-xl backdrop-blur-xl dark:border-gray-700/30 dark:bg-gray-800/50">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Tambah Peneliti</h2>
            <form method="POST" action="{{ route('admin.researchers.store') }}" class="mt-5 space-y-4">
                @csrf
                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nama lengkap</label><input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-xl border-gray-200 bg-white/70 dark:border-gray-600 dark:bg-gray-900/60 dark:text-white">@error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror</div>
                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email</label><input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-xl border-gray-200 bg-white/70 dark:border-gray-600 dark:bg-gray-900/60 dark:text-white">@error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror</div>
                <div><label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal lahir</label><input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required class="mt-1 w-full rounded-xl border-gray-200 bg-white/70 dark:border-gray-600 dark:bg-gray-900/60 dark:text-white">@error('date_of_birth')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror</div>
                <p class="text-xs leading-5 text-gray-500">Akun masuk menggunakan nama lengkap dan tanggal lahir ini.</p>
                <button class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700">Buat Akun Peneliti</button>
            </form>
        </section>

        <section class="rounded-3xl border border-white/20 bg-white/70 p-6 shadow-xl backdrop-blur-xl dark:border-gray-700/30 dark:bg-gray-800/50">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><h2 class="text-lg font-bold text-gray-900 dark:text-white">Daftar Peneliti</h2><p class="text-sm text-gray-500">{{ $researchers->total() }} akun</p></div><form class="flex gap-2"><input name="search" value="{{ request('search') }}" placeholder="Cari nama atau email" class="w-full rounded-xl border-gray-200 bg-white/70 text-sm dark:border-gray-600 dark:bg-gray-900/60 dark:text-white"><button class="rounded-xl border border-indigo-200 px-4 text-sm font-semibold text-indigo-700 dark:border-indigo-500/30 dark:text-indigo-300">Cari</button></form></div>
            <div class="mt-5 overflow-x-auto"><table class="w-full min-w-[620px] text-left text-sm"><thead class="border-b text-xs uppercase text-gray-500 dark:border-gray-700"><tr><th class="px-3 py-3">Peneliti</th><th class="px-3 py-3">Tanggal lahir</th><th class="px-3 py-3">Status</th><th class="px-3 py-3 text-right">Aksi</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @forelse($researchers as $researcher)<tr><td class="px-3 py-4"><p class="font-semibold text-gray-900 dark:text-white">{{ $researcher->name }}</p><p class="text-xs text-gray-500">{{ $researcher->email }}</p></td><td class="px-3 py-4 text-gray-600 dark:text-gray-300">{{ $researcher->date_of_birth?->format('d M Y') ?? '-' }}</td><td class="px-3 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $researcher->is_active ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-gray-500/10 text-gray-600 dark:text-gray-300' }}">{{ $researcher->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td class="px-3 py-4 text-right"><form method="POST" action="{{ route('admin.users.status', $researcher) }}">@csrf @method('PATCH')<button class="rounded-lg px-3 py-2 text-xs font-semibold {{ $researcher->is_active ? 'bg-red-500/10 text-red-600' : 'bg-emerald-500/10 text-emerald-700' }}">{{ $researcher->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></td></tr>@empty<tr><td colspan="4" class="px-3 py-10 text-center text-gray-500">Belum ada akun peneliti.</td></tr>@endforelse
            </tbody></table></div>
            <div class="mt-5">{{ $researchers->links() }}</div>
        </section>
    </div>
</div>
@endsection
