@extends('layouts.app')

@section('title', $editing ? ($profileComplete ? 'Edit Profil' : 'Lengkapi Profil') : 'Profil Saya')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-8 flex items-center gap-4">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 text-lg font-bold text-white shadow-lg shadow-indigo-500/20">{{ substr($user->name, 0, 1) }}</div>
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">{{ $editing ? ($profileComplete ? 'Edit Profil' : 'Lengkapi Profil') : 'Profil Saya' }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $editing ? 'Lengkapi data sesuai kondisi Anda' : 'Informasi responden yang tersimpan' }}</p>
        </div>
    </div>

    @if(! $editing && $profile)
        @php
            $labels = [
                'male' => 'Laki-laki', 'female' => 'Perempuan', 'high-school' => 'SMA/sederajat', 'bachelor' => 'Sarjana', 'master' => 'Magister', 'doctorate' => 'Doktor', 'other' => 'Lainnya',
                'desktop' => 'Komputer desktop', 'laptop' => 'Laptop', 'tablet' => 'Tablet', 'smartphone' => 'Smartphone', 'minimal' => 'Minimal', 'vibrant' => 'Cerah', 'classic' => 'Klasik', 'modern' => 'Modern',
                'home' => 'Rumah', 'office' => 'Kantor', 'school' => 'Sekolah/kampus', 'public' => 'Tempat umum', 'under_1' => '< 1 jam', '1_3' => '1–3 jam', '3_5' => '3–5 jam', '5_8' => '5–8 jam', 'over_8' => '> 8 jam',
                'morning' => 'Pagi', 'afternoon' => 'Siang', 'evening' => 'Sore', 'night' => 'Malam', 'communication' => 'Komunikasi', 'education' => 'Pendidikan', 'shopping' => 'Belanja', 'entertainment' => 'Hiburan', 'finance' => 'Keuangan', 'productivity' => 'Produktivitas', 'social_media' => 'Media sosial',
            ];
            $items = [
                'Data dasar' => [['Usia', $profile->age !== null ? $profile->age.' tahun' : '—'], ['Jenis kelamin', $labels[$profile->gender] ?? '—'], ['Pendidikan', $labels[$profile->education] ?? '—'], ['Pekerjaan', $profile->occupation ?: '—']],
                'Perangkat dan penggunaan' => [['Perangkat', $labels[$profile->deviceType] ?? '—'], ['Durasi harian', $labels[$profile->dailyDeviceUsage] ?? '—'], ['Waktu penggunaan', $labels[$profile->deviceUsageTime] ?? '—'], ['Tujuan utama', $labels[$profile->primaryDevicePurpose] ?? '—'], ['Lingkungan', $labels[$profile->contextEnvironment] ?? '—']],
                'Preferensi dan literasi' => [['Literasi digital', $profile->digitalLiteracy !== null ? $profile->digitalLiteracy.' dari 5' : '—'], ['Preferensi visual', $labels[$profile->visualPreference] ?? '—'], ['Pemahaman AI', $profile->aiFamiliarity !== null ? $profile->aiFamiliarity.' dari 5' : '—']],
            ];
        @endphp
        <section class="rounded-3xl border border-white/20 bg-white/70 p-5 shadow-2xl shadow-indigo-500/10 backdrop-blur-xl dark:border-gray-700/30 dark:bg-gray-800/50 sm:p-8">
            <div class="mb-7 flex items-start justify-between gap-4"><div><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi profil</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Data ini digunakan sebagai karakteristik responden penelitian.</p></div><a href="{{ route('respondent.profile.edit') }}" class="shrink-0 rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 transition hover:from-indigo-700 hover:to-cyan-600">Edit Profil</a></div>
            <div class="space-y-7">
                @foreach($items as $group => $groupItems)
                    <section><h3 class="mb-3 text-xs font-bold uppercase tracking-[.16em] text-indigo-600 dark:text-indigo-400">{{ $group }}</h3><dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">@foreach($groupItems as [$label, $value])<div class="rounded-2xl border border-gray-200/80 bg-white/50 p-4 dark:border-gray-700 dark:bg-gray-900/30"><dt class="text-xs font-medium text-gray-400 dark:text-gray-500">{{ $label }}</dt><dd class="mt-1.5 text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $value }}</dd></div>@endforeach</dl></section>
                @endforeach
            </div>
        </section>
    @else
        <div class="rounded-3xl border border-white/20 bg-white/70 p-5 shadow-2xl shadow-indigo-500/10 backdrop-blur-xl dark:border-gray-700/30 dark:bg-gray-800/50 sm:p-8">
            <form method="POST" action="{{ route('respondent.profile.update') }}" class="space-y-8">@csrf
                <section class="space-y-5"><div><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Data demografis</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Informasi dasar tentang latar belakang Anda.</p></div><div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div><label class="field-label">Jenis Kelamin</label><x-custom-select name="gender" :options="['male' => 'Laki-laki', 'female' => 'Perempuan']" :selected="old('gender', $profile->gender ?? '')" placeholder="Pilih jenis kelamin" />@error('gender')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div><label class="field-label">Pendidikan</label><x-custom-select name="education" :options="['high-school' => 'SMA/sederajat', 'bachelor' => 'Sarjana', 'master' => 'Magister', 'doctorate' => 'Doktor', 'other' => 'Lainnya']" :selected="old('education', $profile->education ?? '')" placeholder="Pilih pendidikan" />@error('education')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div><label class="field-label">Pekerjaan</label><input type="text" name="occupation" value="{{ old('occupation', $profile->occupation ?? '') }}" class="profile-input" placeholder="Pekerjaan Anda">@error('occupation')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div><label class="field-label">Jenis Perangkat</label><x-custom-select name="device_type" :options="['desktop' => 'Komputer desktop', 'laptop' => 'Laptop', 'tablet' => 'Tablet', 'smartphone' => 'Smartphone']" :selected="old('device_type', $profile->deviceType ?? '')" placeholder="Pilih perangkat" />@error('device_type')<p class="field-error">{{ $message }}</p>@enderror</div>
                </div></section>

                <section class="space-y-5 border-t border-gray-200 pt-7 dark:border-gray-700"><div><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Perangkat dan kebiasaan penggunaan</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ceritakan pola penggunaan komputer atau gadget Anda.</p></div><div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div><label class="field-label">Lama penggunaan per hari</label><x-custom-select name="daily_device_usage" :options="['under_1' => '< 1 jam', '1_3' => '1–3 jam', '3_5' => '3–5 jam', '5_8' => '5–8 jam', 'over_8' => '> 8 jam']" :selected="old('daily_device_usage', $profile->dailyDeviceUsage ?? '')" placeholder="Pilih durasi penggunaan" />@error('daily_device_usage')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div><label class="field-label">Waktu penggunaan utama</label><x-custom-select name="device_usage_time" :options="['morning' => 'Pagi', 'afternoon' => 'Siang', 'evening' => 'Sore', 'night' => 'Malam']" :selected="old('device_usage_time', $profile->deviceUsageTime ?? '')" placeholder="Pilih waktu penggunaan" />@error('device_usage_time')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div><label class="field-label">Preferensi visual</label><x-custom-select name="visual_preference" :options="['minimal' => 'Minimal', 'vibrant' => 'Cerah', 'classic' => 'Klasik', 'modern' => 'Modern']" :selected="old('visual_preference', $profile->visualPreference ?? '')" placeholder="Pilih preferensi" />@error('visual_preference')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div><label class="field-label">Lingkungan penggunaan</label><x-custom-select name="context_environment" :options="['home' => 'Rumah', 'office' => 'Kantor', 'school' => 'Sekolah/kampus', 'public' => 'Tempat umum', 'other' => 'Lainnya']" :selected="old('context_environment', $profile->contextEnvironment ?? '')" placeholder="Pilih lingkungan" />@error('context_environment')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="sm:col-span-2"><label class="field-label">Tujuan utama penggunaan</label><x-custom-select name="primary_device_purpose" :options="['communication' => 'Komunikasi', 'education' => 'Pendidikan', 'shopping' => 'Belanja', 'entertainment' => 'Hiburan', 'finance' => 'Keuangan', 'productivity' => 'Produktivitas', 'social_media' => 'Media sosial', 'other' => 'Lainnya']" :selected="old('primary_device_purpose', $profile->primaryDevicePurpose ?? '')" placeholder="Pilih tujuan utama" />@error('primary_device_purpose')<p class="field-error">{{ $message }}</p>@enderror</div>
                </div></section>

                <section class="space-y-5 border-t border-gray-200 pt-7 dark:border-gray-700"><div><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Literasi dan pemahaman AI</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Nilai tingkat kemampuan dan pemahaman Anda.</p></div><div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    @foreach(['digital_literacy' => ['Literasi digital (1–5)', $profile->digitalLiteracy ?? 3], 'ai_familiarity' => ['Pemahaman tentang AI (1–5)', $profile->aiFamiliarity ?? 3]] as $field => [$label, $default])<div><label class="field-label">{{ $label }}</label><div class="mt-2 flex flex-wrap gap-3" x-data="{ value: {{ old($field, $default) }} }"><template x-for="i in 5" :key="i"><button type="button" @click="value = i" class="h-12 w-12 rounded-2xl transition-all duration-200" :class="value >= i ? 'bg-gradient-to-br from-indigo-500 to-cyan-400 text-white shadow-lg shadow-indigo-500/20' : 'border border-gray-200 bg-white/50 text-gray-400 dark:border-gray-600 dark:bg-gray-700/50'" x-text="i"></button></template><input type="hidden" name="{{ $field }}" x-model="value"></div>@error($field)<p class="field-error">{{ $message }}</p>@enderror</div>@endforeach
                </div></section>

                <div class="flex items-center justify-between gap-4 border-t border-gray-200 pt-6 dark:border-gray-700"><a href="{{ $profileComplete ? route('respondent.profile.show') : route('respondent.dashboard') }}" class="text-sm text-gray-500 transition-colors hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-300">Batal</a><button type="submit" class="rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition-all duration-200 hover:from-indigo-700 hover:to-cyan-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:px-8">Simpan Profil</button></div>
            </form>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .field-label { display: block; margin-bottom: .375rem; font-size: .875rem; font-weight: 500; color: #374151; }
    .profile-input { width: 100%; border-radius: 1rem; border: 1px solid #e5e7eb; background: rgb(255 255 255 / .5); padding: .75rem 1rem; color: #111827; }
    .field-error { margin-top: .25rem; font-size: .875rem; color: #ef4444; }
    .dark .field-label { color: #d1d5db; }
    .dark .profile-input { border-color: #4b5563; background: rgb(55 65 81 / .5); color: white; }
</style>
@endpush
