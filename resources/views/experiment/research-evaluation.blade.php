@extends('layouts.app')

@section('title', 'Evaluasi Eksperimen')

@section('content')
<div class="max-w-2xl mx-auto" x-data="researchEvaluation()">
    <div class="text-center mb-8">
        <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">Langkah terakhir</p>
        <h1 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">Evaluasi Pengalaman</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-300">Nilai pengalaman Anda setelah menyelesaikan seluruh tugas preferensi.</p>
    </div>

    <form @submit.prevent="submit" class="rounded-3xl border border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-800/70 p-6 sm:p-8 space-y-7">
        <fieldset>
            <legend class="font-semibold text-gray-900 dark:text-white">Penggunaan aplikasi berbasis AI</legend>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Seberapa sering Anda menggunakan aplikasi yang berbasis AI?</p>
            <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-5">
                @foreach(['never' => 'Tidak pernah', 'rarely' => 'Jarang', 'sometimes' => 'Kadang-kadang', 'often' => 'Sering', 'very_often' => 'Sering sekali'] as $value => $label)
                    <label class="cursor-pointer"><input type="radio" class="sr-only peer" x-model="form.ai_usage_frequency" value="{{ $value }}" required><span class="flex min-h-12 items-center justify-center rounded-xl border border-gray-200 px-2 text-center text-sm dark:border-gray-600 peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:text-white">{{ $label }}</span></label>
                @endforeach
            </div>
        </fieldset>

        @foreach([
            'ai_answer_trust' => ['Kepercayaan pada AI', 'Seberapa banyak Anda mempercayai jawaban atau rekomendasi AI?'],
            'ai_explanation_preference' => ['Preferensi penjelasan AI', 'Apakah Anda lebih memilih sistem AI yang menjelaskan rekomendasi atau jawabannya?'],
        ] as $field => [$label, $help])
            <fieldset>
                <legend class="font-semibold text-gray-900 dark:text-white">{{ $label }}</legend>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $help }}</p>
                <div class="mt-3 grid grid-cols-5 gap-2">
                    @foreach(range(1, 5) as $score)
                        <label class="cursor-pointer"><input type="radio" class="sr-only peer" x-model.number="form.{{ $field }}" value="{{ $score }}" required><span class="flex h-12 items-center justify-center rounded-xl border border-gray-200 dark:border-gray-600 peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:text-white">{{ $score }}</span></label>
                    @endforeach
                </div>
                <div class="mt-2 flex justify-between text-xs text-gray-400"><span>1 = sangat rendah</span><span>5 = sangat tinggi</span></div>
            </fieldset>
        @endforeach

        @foreach([
            'trust_score' => ['Kepercayaan', 'Seberapa yakin Anda terhadap hasil konfigurasi UI?'],
            'transparency_score' => ['Transparansi', 'Seberapa jelas proses pembentukan preferensinya?'],
            'ui_satisfaction' => ['Kepuasan terhadap UI', 'Seberapa puas Anda dengan pengalaman keseluruhan?'],
        ] as $field => [$label, $help])
            <fieldset>
                <legend class="font-semibold text-gray-900 dark:text-white">{{ $label }}</legend>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $help }}</p>
                <div class="mt-3 grid grid-cols-5 gap-2">
                    @foreach(range(1, 5) as $score)
                        <label class="cursor-pointer">
                            <input type="radio" class="sr-only peer" x-model.number="form.{{ $field }}" value="{{ $score }}" required>
                            <span class="flex h-12 items-center justify-center rounded-xl border border-gray-200 dark:border-gray-600 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600">{{ $score }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endforeach

        <div>
            <label for="feedback" class="font-semibold text-gray-900 dark:text-white">Masukan opsional</label>
            <textarea id="feedback" x-model="form.feedback" maxlength="5000" rows="4" class="mt-3 w-full rounded-2xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900" placeholder="Ceritakan bagian yang jelas, membingungkan, atau perlu diperbaiki..."></textarea>
        </div>

        <p x-show="error" x-text="error" class="text-sm text-red-600"></p>
        <button type="submit" :disabled="submitting" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 text-white font-semibold disabled:opacity-50">
            <span x-text="submitting ? 'Menyimpan…' : 'Kirim Evaluasi & Selesaikan'"></span>
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
function researchEvaluation() {
    return {
        form: { trust_score: 3, transparency_score: 3, ui_satisfaction: 3, ai_usage_frequency: '', ai_answer_trust: 3, ai_explanation_preference: 3, feedback: '' },
        submitting: false,
        error: '',
        async submit() {
            this.submitting = true;
            this.error = '';
            const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content };
            try {
                const evaluation = await fetch(@js(route('respondent.preference-experiment.evaluation', $experiment['id'])), { method: 'POST', headers, body: JSON.stringify(this.form) });
                const evaluationResult = await evaluation.json();
                if (!evaluation.ok) throw new Error(evaluationResult.message || 'Evaluasi tidak dapat disimpan.');
                const completion = await fetch(@js(route('respondent.preference-experiment.complete', $experiment['id'])), { method: 'POST', headers });
                const completionResult = await completion.json();
                if (!completion.ok) throw new Error(completionResult.message || 'Eksperimen tidak dapat diselesaikan.');
                window.location.href = @js(route('respondent.preference-experiment.complete.page', $experiment['id']));
            } catch (error) {
                this.error = error.message;
                this.submitting = false;
            }
        },
    };
}
</script>
@endpush
