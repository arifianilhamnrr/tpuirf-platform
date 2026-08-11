@extends('layouts.app')

@section('title', 'Kelola Responden')

@section('content')
<div class="space-y-6">
    <div><p class="text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Administrasi</p><h1 class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">Kelola Responden</h1><p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Pantau partisipasi dan nonaktifkan akses tanpa menghapus data penelitian.</p></div>
    @include('admin._tabs')
    <section class="rounded-3xl border border-white/20 bg-white/70 p-6 shadow-xl backdrop-blur-xl dark:border-gray-700/30 dark:bg-gray-800/50">
        <form class="grid gap-3 sm:grid-cols-[1fr_180px_auto]"><input name="search" value="{{ request('search') }}" placeholder="Cari nama atau email" class="rounded-xl border-gray-200 bg-white/70 text-sm dark:border-gray-600 dark:bg-gray-900/60 dark:text-white"><select name="status" class="rounded-xl border-gray-200 bg-white/70 text-sm dark:border-gray-600 dark:bg-gray-900/60 dark:text-white"><option value="">Semua status</option><option value="active" @selected(request('status') === 'active')>Aktif</option><option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option></select><button class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white">Terapkan</button></form>
        <div class="mt-5 overflow-x-auto"><table class="w-full min-w-[760px] text-left text-sm"><thead class="border-b text-xs uppercase text-gray-500 dark:border-gray-700"><tr><th class="px-3 py-3">Responden</th><th class="px-3 py-3">Tanggal lahir</th><th class="px-3 py-3">Eksperimen</th><th class="px-3 py-3">Rekomendasi</th><th class="px-3 py-3">Status</th><th class="px-3 py-3 text-right">Aksi</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
            @forelse($respondents as $respondent)<tr><td class="px-3 py-4"><p class="font-semibold text-gray-900 dark:text-white">{{ $respondent->name }}</p><p class="text-xs text-gray-500">{{ $respondent->email }}</p></td><td class="px-3 py-4">{{ $respondent->date_of_birth?->format('d M Y') ?? '-' }}</td><td class="px-3 py-4 font-semibold">{{ $respondent->experiments_count }}</td><td class="px-3 py-4 font-semibold">{{ $respondent->recommendations_count }}</td><td class="px-3 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $respondent->is_active ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-gray-500/10 text-gray-600 dark:text-gray-300' }}">{{ $respondent->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td class="px-3 py-4 text-right"><form method="POST" action="{{ route('admin.users.status', $respondent) }}">@csrf @method('PATCH')<button class="rounded-lg px-3 py-2 text-xs font-semibold {{ $respondent->is_active ? 'bg-red-500/10 text-red-600' : 'bg-emerald-500/10 text-emerald-700' }}">{{ $respondent->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form></td></tr>@empty<tr><td colspan="6" class="px-3 py-10 text-center text-gray-500">Tidak ada responden yang cocok.</td></tr>@endforelse
        </tbody></table></div><div class="mt-5">{{ $respondents->links() }}</div>
    </section>
</div>
@endsection
