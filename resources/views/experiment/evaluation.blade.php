@extends('layouts.app')

@section('title', 'Evaluasi')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Evaluasi</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Silakan nilai pengalaman Anda</p>
        </div>

        <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-8 border border-white/20 dark:border-gray-700/30 shadow-xl">
            <form method="POST" action="{{ route('respondent.experiment.submit-evaluation') }}" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Skor Kepercayaan</label>
                    <div class="flex gap-3" x-data="{ value: 3 }">
                        <template x-for="i in 5" :key="i">
                            <button type="button" @click="value = i" class="flex-1 py-4 rounded-2xl text-center transition-all duration-200" :class="value >= i ? 'bg-gradient-to-br from-indigo-500 to-cyan-400 text-white shadow-lg shadow-indigo-500/20' : 'bg-white/50 dark:bg-gray-700/50 text-gray-400 border border-gray-200 dark:border-gray-600'">
                                <span class="block text-lg font-bold" x-text="i"></span>
                                <span class="block text-xs mt-1" x-text="['Sangat Rendah', 'Rendah', 'Netral', 'Tinggi', 'Sangat Tinggi'][i-1]"></span>
                            </button>
                        </template>
                        <input type="hidden" name="trust_score" x-model="value">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Skor Transparansi</label>
                    <div class="flex gap-3" x-data="{ value: 3 }">
                        <template x-for="i in 5" :key="i">
                            <button type="button" @click="value = i" class="flex-1 py-4 rounded-2xl text-center transition-all duration-200" :class="value >= i ? 'bg-gradient-to-br from-purple-500 to-pink-400 text-white shadow-lg shadow-purple-500/20' : 'bg-white/50 dark:bg-gray-700/50 text-gray-400 border border-gray-200 dark:border-gray-600'">
                                <span class="block text-lg font-bold" x-text="i"></span>
                                <span class="block text-xs mt-1" x-text="['Sangat Rendah', 'Rendah', 'Netral', 'Tinggi', 'Sangat Tinggi'][i-1]"></span>
                            </button>
                        </template>
                        <input type="hidden" name="transparency_score" x-model="value">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Kepuasan terhadap UI</label>
                    <div class="flex gap-3" x-data="{ value: 3 }">
                        <template x-for="i in 5" :key="i">
                            <button type="button" @click="value = i" class="flex-1 py-4 rounded-2xl text-center transition-all duration-200" :class="value >= i ? 'bg-gradient-to-br from-cyan-500 to-teal-400 text-white shadow-lg shadow-cyan-500/20' : 'bg-white/50 dark:bg-gray-700/50 text-gray-400 border border-gray-200 dark:border-gray-600'">
                                <span class="block text-lg font-bold" x-text="i"></span>
                                <span class="block text-xs mt-1" x-text="['Sangat Tidak Puas', 'Tidak Puas', 'Netral', 'Puas', 'Sangat Puas'][i-1]"></span>
                            </button>
                        </template>
                        <input type="hidden" name="ui_satisfaction" x-model="value">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Masukan (Opsional)</label>
                    <textarea name="feedback" rows="4" class="w-full px-4 py-3 rounded-2xl bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Ceritakan pengalaman Anda..."></textarea>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 text-white font-semibold hover:from-indigo-700 hover:to-cyan-600 transition-all duration-200 shadow-lg shadow-indigo-500/25">
                    Kirim Evaluasi
                </button>
            </form>
        </div>
    </div>
@endsection
