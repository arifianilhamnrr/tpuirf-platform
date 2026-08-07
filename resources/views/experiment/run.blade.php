@extends('layouts.app')

@section('title', 'Eksperimen - Versi UI '.$uiVersion)

@section('content')
    <div class="max-w-4xl mx-auto" x-data="interactionLogger()" x-init="init('{{ $experiment->id }}', '{{ $uiVersion }}', {{ $step }})">
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Eksperimen: Versi UI {{ $uiVersion }}</h1>
                <span class="px-4 py-1.5 rounded-xl backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 text-sm font-medium text-gray-600 dark:text-gray-300 border border-white/20 dark:border-gray-700/30">
                    Langkah {{ $step }} dari {{ $totalSteps }}
                </span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-indigo-500 to-cyan-400 rounded-full transition-all duration-500" style="width: {{ ($step / $totalSteps) * 100 }}%"></div>
            </div>
        </div>

        <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 sm:p-8 border border-white/20 dark:border-gray-700/30 shadow-xl mb-6" id="ui-container" :style="uiStyle">
            <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-indigo-500 to-cyan-400 flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-indigo-500/20">
                    {{ $uiVersion }}
                </div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Tugas {{ $step }}</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Silakan berinteraksi dengan versi antarmuka ini. Cobalah mengeklik, menggulir, dan menjelajahi elemen di bawah.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                <div class="p-6 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 border border-indigo-100 dark:border-indigo-800/30 cursor-pointer hover:shadow-lg transition-all duration-200" @click="logEvent('click', 'card-1')" @dblclick="logEvent('dblclick', 'card-1')" @mouseenter="logEvent('hover', 'card-1')">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </div>
                    <h3 class="font-semibold text-gray-800 dark:text-white">Kartu Fitur</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Klik dan jelajahi kartu ini</p>
                </div>
                <div class="p-6 rounded-2xl bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-900/20 dark:to-teal-900/20 border border-cyan-100 dark:border-cyan-800/30 cursor-pointer hover:shadow-lg transition-all duration-200" @click="logEvent('click', 'card-2')" @dblclick="logEvent('dblclick', 'card-2')" @mouseenter="logEvent('hover', 'card-2')">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-teal-500 flex items-center justify-center text-white mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="font-semibold text-gray-800 dark:text-white">Kartu Analitik</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Klik dua kali untuk menjelajahi</p>
                </div>
            </div>

            <div class="space-y-3 mb-8">
                <div class="flex items-center gap-3 p-4 rounded-2xl bg-white/50 dark:bg-gray-700/30 cursor-pointer hover:bg-white/80 dark:hover:bg-gray-700/50 transition-all duration-200" @click="logEvent('click', 'list-item-1')">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-xs font-bold">1</div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white">Contoh Item Tugas</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Klik untuk memilih item ini</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-4 rounded-2xl bg-white/50 dark:bg-gray-700/30 cursor-pointer hover:bg-white/80 dark:hover:bg-gray-700/50 transition-all duration-200" @click="logEvent('click', 'list-item-2')">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center text-white text-xs font-bold">2</div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white">Item Tugas Lainnya</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Klik untuk memilih item ini</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-4 rounded-2xl bg-white/50 dark:bg-gray-700/30 cursor-pointer hover:bg-white/80 dark:hover:bg-gray-700/50 transition-all duration-200" @click="logEvent('click', 'list-item-3')">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center text-white text-xs font-bold">3</div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white">Item Tugas Ketiga</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Klik untuk memilih item ini</p>
                    </div>
                </div>
            </div>

            <div @scroll.debounce="logEvent('scroll', 'scroll-area')" class="h-32 overflow-y-auto p-4 rounded-2xl bg-white/30 dark:bg-gray-700/20 border border-gray-200 dark:border-gray-600 mb-6" id="scroll-area">
                <p class="text-sm text-gray-500 dark:text-gray-400">Area konten yang dapat digulir. Cobalah menggulir konten ini untuk melihat cara pencatat interaksi menangkap aktivitas gulir. Setiap interaksi otomatis direkam untuk analisis.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">Masih ada konten di sini. Terus gulir untuk melihat contoh tambahan dari fungsi pelacakan gulir.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">Konten tambahan lainnya. Pencatat interaksi merekam aktivitas pengguna untuk analisis perilaku dan personalisasi.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">Bagian terakhir. Terima kasih telah menggulir konten ini. Interaksi Anda membantu meningkatkan sistem rekomendasi.</p>
            </div>

            <input type="text" placeholder="Ketik sesuatu di sini..." @focus="logEvent('focus', 'text-input')" @blur="logEvent('blur', 'text-input')" @keydown="logEvent('keyboard', 'text-input')" class="w-full px-4 py-3 rounded-2xl bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent mb-6">
        </div>

        <div class="flex justify-between items-center">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <span x-text="`Interaksi: ${interactionCount}`"></span>
                <span class="mx-2">|</span>
                <span x-text="`Waktu: ${Math.floor(elapsedTime / 60)}:${(elapsedTime % 60).toString().padStart(2, '0')}`"></span>
            </div>
            <form method="POST" action="{{ route('respondent.experiment.complete-step') }}">
                @csrf
                <button type="submit" class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-cyan-500 text-white font-semibold hover:from-indigo-700 hover:to-cyan-600 transition-all duration-200 shadow-lg shadow-indigo-500/25 transform hover:scale-[1.02] active:scale-[0.98]">
                    @if($step < $totalSteps)
                        Langkah Berikutnya
                    @else
                        Lanjut ke Preferensi
                    @endif
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function interactionLogger() {
        return {
            experimentId: null,
            uiVersion: null,
            step: null,
            interactionCount: 0,
            elapsedTime: 0,
            startTime: null,
            timerInterval: null,
            logs: [],

            init(experimentId, uiVersion, step) {
                this.experimentId = experimentId;
                this.uiVersion = uiVersion;
                this.step = step;
                this.startTime = Date.now();
                this.timerInterval = setInterval(() => {
                    this.elapsedTime = Math.floor((Date.now() - this.startTime) / 1000);
                }, 1000);
                this.setupGlobalListeners();
            },

            setupGlobalListeners() {
                window.addEventListener('resize', () => {
                    this.logEvent('window_resize', 'window', { width: window.innerWidth, height: window.innerHeight });
                });
                document.addEventListener('mousemove', this.debounce((e) => {
                    this.logEvent('mouse_move', 'document', { x: e.clientX, y: e.clientY });
                }, 500));
                document.addEventListener('keydown', (e) => {
                    if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                        this.logEvent('keyboard', 'document', { key: e.key });
                    }
                });
            },

            logEvent(eventType, target, extraData = {}) {
                this.interactionCount++;
                const log = {
                    experiment_id: this.experimentId,
                    user_id: document.querySelector('meta[name="user-id"]')?.content || '',
                    ui_version: this.uiVersion,
                    event_type: eventType,
                    event_data: extraData,
                    target_element: target,
                    page_url: window.location.href,
                    viewport_width: window.innerWidth,
                    viewport_height: window.innerHeight,
                    browser: navigator.userAgent,
                    os: navigator.platform,
                    task_time: this.elapsedTime,
                    session_time: this.elapsedTime,
                    timestamp: new Date().toISOString(),
                };
                this.logs.push(log);
                this.sendLog(log);
            },

            sendLog(log) {
                if (navigator.sendBeacon) {
                    navigator.sendBeacon('{{ route('interaction.log') }}', JSON.stringify(log));
                } else {
                    fetch('{{ route('interaction.log') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify(log),
                    });
                }
            },

            debounce(func, wait) {
                let timeout;
                return (...args) => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            },
        };
    }
</script>
@endpush
