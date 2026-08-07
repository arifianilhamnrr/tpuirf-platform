@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold bg-gradient-to-r from-indigo-600 to-cyan-500 bg-clip-text text-transparent">TPUIRF</h1>
            <p class="mt-2 text-gray-500 dark:text-gray-400 text-sm">Kerangka Rekomendasi Antarmuka Personal yang Transparan</p>
        </div>
        <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-8 shadow-2xl shadow-indigo-500/10 border border-white/20 dark:border-gray-700/30">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Selamat Datang</h2>
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Lengkap</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus class="w-full px-4 py-3 rounded-2xl bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" placeholder="Masukkan nama lengkap Anda">
                    @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Lahir</label>
                    <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required class="w-full px-4 py-3 rounded-2xl bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
                    @error('date_of_birth') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 text-white font-semibold text-sm hover:from-indigo-700 hover:to-cyan-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-indigo-500/25">
                    Masuk
                </button>
                <p class="text-center text-xs text-gray-400 dark:text-gray-500">
                    Masukkan nama lengkap dan tanggal lahir Anda untuk masuk. Jika belum terdaftar, akun akan dibuat otomatis.
                </p>
            </form>
        </div>
    </div>
@endsection
