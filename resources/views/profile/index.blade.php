@extends('layouts.app')

@section('title', $editing ? ($profileComplete ? 'Edit Profil' : 'Lengkapi Profil') : 'Profil Saya')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-2">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-indigo-500/20">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">
                        {{ $editing ? ($profileComplete ? 'Edit Profil' : 'Lengkapi Profil') : 'Profil Saya' }}
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        {{ $editing ? 'Lengkapi data sesuai kondisi Anda' : 'Informasi responden yang tersimpan' }}
                    </p>
                </div>
            </div>
        </div>

        @if(! $editing && $profile)
            @php
                $genderLabels = ['male' => 'Laki-laki', 'female' => 'Perempuan'];
                $educationLabels = ['high-school' => 'SMA/sederajat', 'bachelor' => 'Sarjana', 'master' => 'Magister', 'doctorate' => 'Doktor', 'other' => 'Lainnya'];
                $deviceLabels = ['desktop' => 'Komputer desktop', 'laptop' => 'Laptop', 'tablet' => 'Tablet', 'smartphone' => 'Smartphone'];
                $visualLabels = ['minimal' => 'Minimal', 'vibrant' => 'Cerah', 'classic' => 'Klasik', 'modern' => 'Modern'];
                $environmentLabels = ['home' => 'Rumah', 'office' => 'Kantor', 'school' => 'Sekolah/kampus', 'public' => 'Tempat umum', 'other' => 'Lainnya'];
                $profileItems = [
                    ['label' => 'Usia', 'value' => $profile->age !== null ? $profile->age.' tahun' : '—'],
                    ['label' => 'Jenis Kelamin', 'value' => $genderLabels[$profile->gender] ?? '—'],
                    ['label' => 'Pendidikan', 'value' => $educationLabels[$profile->education] ?? '—'],
                    ['label' => 'Pekerjaan', 'value' => $profile->occupation ?: '—'],
                    ['label' => 'Literasi Digital', 'value' => $profile->digitalLiteracy !== null ? $profile->digitalLiteracy.' dari 5' : '—'],
                    ['label' => 'Jenis Perangkat', 'value' => $deviceLabels[$profile->deviceType] ?? '—'],
                    ['label' => 'Preferensi Visual', 'value' => $visualLabels[$profile->visualPreference] ?? '—'],
                    ['label' => 'Lingkungan Penggunaan', 'value' => $environmentLabels[$profile->contextEnvironment] ?? '—'],
                    ['label' => 'Pemahaman tentang AI', 'value' => $profile->aiFamiliarity !== null ? $profile->aiFamiliarity.' dari 5' : '—'],
                ];
            @endphp

            <section class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 sm:p-8 shadow-2xl shadow-indigo-500/10 border border-white/20 dark:border-gray-700/30">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Informasi Profil</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Data ini digunakan sebagai karakteristik responden penelitian.</p>
                    </div>
                    <a href="{{ route('respondent.profile.edit') }}" class="inline-flex shrink-0 items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 transition hover:from-indigo-700 hover:to-cyan-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 13H9v-2.828l6.586-6.586z"/></svg>
                        Edit Profil
                    </a>
                </div>

                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach($profileItems as $item)
                        <div class="rounded-2xl border border-gray-200/80 bg-white/50 p-4 dark:border-gray-700 dark:bg-gray-900/30">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ $item['label'] }}</dt>
                            <dd class="mt-1.5 text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $item['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @else
        <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-8 shadow-2xl shadow-indigo-500/10 border border-white/20 dark:border-gray-700/30">
            <form method="POST" action="{{ route('respondent.profile.update') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jenis Kelamin</label>
                        <x-custom-select 
                            name="gender" 
                            :options="['male' => 'Laki-laki', 'female' => 'Perempuan']" 
                            :selected="old('gender', $profile->gender ?? '')" 
                            placeholder="Pilih jenis kelamin" 
                        />
                        @error('gender') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Pendidikan</label>
                        <x-custom-select 
                            name="education" 
                            :options="['high-school' => 'SMA/sederajat', 'bachelor' => 'Sarjana', 'master' => 'Magister', 'doctorate' => 'Doktor', 'other' => 'Lainnya']" 
                            :selected="old('education', $profile->education ?? '')" 
                            placeholder="Pilih pendidikan" 
                        />
                        @error('education') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Pekerjaan</label>
                        <input type="text" name="occupation" value="{{ old('occupation', $profile->occupation ?? '') }}" class="w-full px-4 py-3 rounded-2xl bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Pekerjaan Anda">
                        @error('occupation') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Literasi Digital (1–5)</label>
                        <div class="flex gap-3" x-data="{ value: {{ old('digital_literacy', $profile->digitalLiteracy ?? 3) }} }">
                            <template x-for="i in 5" :key="i">
                                <button type="button" @click="value = i" class="w-12 h-12 rounded-2xl transition-all duration-200" :class="value >= i ? 'bg-gradient-to-br from-indigo-500 to-cyan-400 text-white shadow-lg shadow-indigo-500/20' : 'bg-white/50 dark:bg-gray-700/50 text-gray-400 border border-gray-200 dark:border-gray-600'" x-text="i"></button>
                            </template>
                            <input type="hidden" name="digital_literacy" x-model="value">
                        </div>
                        @error('digital_literacy') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jenis Perangkat</label>
                        <x-custom-select 
                            name="device_type" 
                            :options="['desktop' => 'Komputer desktop', 'laptop' => 'Laptop', 'tablet' => 'Tablet', 'smartphone' => 'Smartphone']" 
                            :selected="old('device_type', $profile->deviceType ?? '')" 
                            placeholder="Pilih perangkat" 
                        />
                        @error('device_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Preferensi Visual</label>
                        <x-custom-select 
                            name="visual_preference" 
                            :options="['minimal' => 'Minimal', 'vibrant' => 'Cerah', 'classic' => 'Klasik', 'modern' => 'Modern']" 
                            :selected="old('visual_preference', $profile->visualPreference ?? '')" 
                            placeholder="Pilih preferensi" 
                        />
                        @error('visual_preference') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Lingkungan Penggunaan</label>
                        <x-custom-select 
                            name="context_environment" 
                            :options="['home' => 'Rumah', 'office' => 'Kantor', 'school' => 'Sekolah/kampus', 'public' => 'Tempat umum', 'other' => 'Lainnya']" 
                            :selected="old('context_environment', $profile->contextEnvironment ?? '')" 
                            placeholder="Pilih lingkungan" 
                        />
                        @error('context_environment') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Pemahaman tentang AI (1–5)</label>
                        <div class="flex gap-3" x-data="{ value: {{ old('ai_familiarity', $profile->aiFamiliarity ?? 3) }} }">
                            <template x-for="i in 5" :key="i">
                                <button type="button" @click="value = i" class="w-12 h-12 rounded-2xl transition-all duration-200" :class="value >= i ? 'bg-gradient-to-br from-indigo-500 to-cyan-400 text-white shadow-lg shadow-indigo-500/20' : 'bg-white/50 dark:bg-gray-700/50 text-gray-400 border border-gray-200 dark:border-gray-600'" x-text="i"></button>
                        </template>
                        <input type="hidden" name="ai_familiarity" x-model="value">
                    </div>
                    @error('ai_familiarity') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ $profileComplete ? route('respondent.profile.show') : route('respondent.dashboard') }}" class="text-sm text-gray-500 transition-colors hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-300">Batal</a>
                <button type="submit" class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 text-white font-semibold text-sm hover:from-indigo-700 hover:to-cyan-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200 shadow-lg shadow-indigo-500/25">
                    Simpan Profil
                </button>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
