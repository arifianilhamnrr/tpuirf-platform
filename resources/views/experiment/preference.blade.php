@extends('layouts.app')

@section('title', 'Pilih UI Favorit')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Pilih UI yang Anda Sukai</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Versi UI mana yang paling Anda sukai?</p>
        </div>

        <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-8 border border-white/20 dark:border-gray-700/30 shadow-xl">
            <form method="POST" action="{{ route('respondent.experiment.submit-preference') }}">
                @csrf
                <div class="grid grid-cols-2 gap-4 mb-8">
                    @foreach($uiVersions as $version)
                        <label class="cursor-pointer" x-data="{ selected: false }">
                            <input type="radio" name="preferred_ui" value="{{ $version }}" class="hidden" @change="selected = true">
                            <div class="p-6 rounded-2xl border-2 transition-all duration-200" :class="selected ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 shadow-lg' : 'border-gray-200 dark:border-gray-600 hover:border-indigo-300'">
                                <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                                    {{ $version }}
                                </div>
                                <p class="text-center font-semibold text-gray-800 dark:text-white">Versi UI {{ $version }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
                <button type="submit" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 text-white font-semibold hover:from-indigo-700 hover:to-cyan-600 transition-all duration-200 shadow-lg shadow-indigo-500/25">
                    Kirim Preferensi
                </button>
            </form>
        </div>
    </div>
@endsection
