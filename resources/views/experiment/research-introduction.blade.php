@extends('layouts.app')

@section('title', 'Penelitian Preferensi')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="text-center">
            <p class="text-sm font-semibold tracking-wider text-indigo-600 dark:text-indigo-400 uppercase">{{ $protocolVersion }}</p>
            <h1 class="mt-2 text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Penelitian Preferensi Antarmuka</h1>
            <p class="mt-3 text-gray-600 dark:text-gray-300">Anda akan membandingkan enam aspek antarmuka. Tidak ada jawaban benar atau salah—pilih yang paling nyaman menurut Anda.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($tasks as $key => $task)
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white/70 dark:bg-gray-800/60 p-5">
                    <span class="inline-flex w-8 h-8 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-bold">{{ $loop->iteration }}</span>
                    <h2 class="mt-3 font-semibold text-gray-900 dark:text-white">{{ $task['label'] }}</h2>
                </div>
            @endforeach
        </div>

        <div class="rounded-3xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50/80 dark:bg-indigo-950/30 p-6 sm:p-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Persetujuan partisipasi</h2>
            <div class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                <p>Sistem akan mencatat waktu aktif/diam, klik per komponen, perpindahan klik, serta ringkasan gulir selama eksperimen.</p>
                <p>Sistem tidak mencatat isi teks yang Anda ketik, token, kuki, atau tombol papan ketik mentah.</p>
            </div>

            <form method="POST" action="{{ route('respondent.preference-experiment.start') }}" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="consent_version" value="{{ $consentVersion }}">
                <label class="flex items-start gap-3 rounded-2xl bg-white/80 dark:bg-gray-800/70 p-4 cursor-pointer">
                    <input type="checkbox" name="consent_accepted" value="1" required class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700 dark:text-gray-200">Saya memahami informasi di atas dan bersedia mengikuti eksperimen ini.</span>
                </label>
                @error('consent_accepted')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <button type="submit" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 text-white font-semibold shadow-lg shadow-indigo-500/20">
                    Mulai Eksperimen
                </button>
            </form>
        </div>
    </div>
@endsection
