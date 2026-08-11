<nav class="sticky top-0 z-50 border-b border-white/20 bg-white/70 backdrop-blur-xl dark:border-gray-700/30 dark:bg-gray-800/50" x-data="{ mobileOpen: false, userMenuOpen: false }" @keydown.escape.window="userMenuOpen = false; mobileOpen = false">
    @php
        $role = session('user_role');
        $navItems = match($role) {
            'admin' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
                ['label' => 'Responden', 'route' => 'admin.respondents'],
                ['label' => 'Eksperimen', 'route' => 'admin.experiments'],
                ['label' => 'Analitik', 'route' => 'analytics.index'],
            ],
            'researcher' => [
                ['label' => 'Dashboard', 'route' => 'researcher.dashboard'],
                ['label' => 'Analitik', 'route' => 'analytics.index'],
                ['label' => 'Ekspor', 'route' => 'export.csv'],
            ],
            default => [
                ['label' => 'Dashboard', 'route' => 'respondent.dashboard'],
                ['label' => 'Rekomendasi', 'route' => 'respondent.recommendation.show'],
            ],
        };
        $roleLabel = match($role) {
            'admin' => 'Administrator',
            'researcher' => 'Peneliti',
            default => 'Responden',
        };
        $userInitial = mb_strtoupper(mb_substr((string) session('user_name'), 0, 1));
    @endphp

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="{{ route($role.'.dashboard') }}" class="bg-gradient-to-r from-indigo-600 to-cyan-500 bg-clip-text text-xl font-extrabold text-transparent">TPUIRF</a>
                <div class="hidden items-center gap-1 md:flex">
                    @foreach($navItems as $item)
                        <a href="{{ route($item['route']) }}" class="rounded-xl px-4 py-2 text-sm font-medium transition-all duration-200 {{ request()->routeIs($item['route']) ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <div class="relative hidden md:block" @click.outside="userMenuOpen = false">
                    <button type="button" id="user-menu-button" @click="userMenuOpen = !userMenuOpen; mobileOpen = false" :aria-expanded="userMenuOpen" aria-controls="user-menu-dropdown" aria-haspopup="menu" aria-label="Buka menu pengguna" title="Menu pengguna" class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-cyan-400 text-sm font-bold text-white shadow-md shadow-indigo-500/20 transition hover:scale-105 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        {{ $userInitial }}
                    </button>

                    <div id="user-menu-dropdown" x-show="userMenuOpen" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-1 scale-95" role="menu" aria-labelledby="user-menu-button" class="absolute right-0 z-[70] mt-2 w-64 origin-top-right overflow-hidden rounded-2xl border border-gray-200 bg-white/95 p-2 shadow-2xl backdrop-blur-xl dark:border-gray-700 dark:bg-gray-800/95">
                        <div class="px-3 py-2">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ session('user_name') }}</p>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $roleLabel }}</p>
                        </div>

                        <div class="my-1 border-t border-gray-200 dark:border-gray-700"></div>

                        @if($role === 'respondent')
                            <a href="{{ route('respondent.profile.show') }}" @click="userMenuOpen = false" aria-label="Buka profil" role="menuitem" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors {{ request()->routeIs('respondent.profile.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 dark:text-gray-200 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-300' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.12a7.5 7.5 0 0 1 15 0A17.9 17.9 0 0 1 12 21.75a17.9 17.9 0 0 1-7.5-1.63Z"/></svg>
                                <span>Profil</span>
                            </a>
                        @endif

                        <button type="button" @click="darkMode = !darkMode" :aria-label="darkMode ? 'Gunakan mode terang' : 'Gunakan mode gelap'" role="menuitem" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-gray-700 transition-colors hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700">
                            <svg x-show="!darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.35 15.35A9 9 0 0 1 8.65 3.65 9 9 0 1 0 20.35 15.35Z"/></svg>
                            <svg x-show="darkMode" class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.36 6.36-.7-.7M6.34 6.34l-.7-.7m12.72 0-.7.7M6.34 17.66l-.7.7M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/></svg>
                            <span x-text="darkMode ? 'Mode Terang' : 'Mode Gelap'"></span>
                        </button>

                        <div class="my-1 border-t border-gray-200 dark:border-gray-700"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" aria-label="Keluar" role="menuitem" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-3H9.75m9 0-3-3m3 3-3 3"/></svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>

                <button type="button" :aria-label="mobileOpen ? 'Tutup menu' : 'Buka menu'" :title="mobileOpen ? 'Tutup menu' : 'Buka menu'" @click="mobileOpen = !mobileOpen; userMenuOpen = false" class="rounded-xl p-2 text-gray-600 dark:text-gray-300 md:hidden">
                    <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileOpen" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="mobileOpen" x-cloak class="space-y-1 border-t border-white/20 p-4 dark:border-gray-700/30 md:hidden">
        <div class="mb-3 flex items-center gap-3 rounded-2xl bg-white/60 p-3 dark:bg-gray-700/40">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-cyan-400 text-sm font-bold text-white">{{ $userInitial }}</span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ session('user_name') }}</p>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $roleLabel }}</p>
            </div>
        </div>

        @foreach($navItems as $item)
            <a href="{{ route($item['route']) }}" class="block rounded-xl px-4 py-2.5 text-sm font-medium {{ request()->routeIs($item['route']) ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700/50' }}">
                {{ $item['label'] }}
            </a>
        @endforeach

        <div class="my-2 border-t border-gray-200 dark:border-gray-700"></div>

        @if($role === 'respondent')
            <a href="{{ route('respondent.profile.show') }}" aria-label="Buka profil" class="flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 dark:text-gray-200 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.12a7.5 7.5 0 0 1 15 0A17.9 17.9 0 0 1 12 21.75a17.9 17.9 0 0 1-7.5-1.63Z"/></svg>
                <span>Profil</span>
            </a>
        @endif

        <button type="button" @click="darkMode = !darkMode" :aria-label="darkMode ? 'Gunakan mode terang' : 'Gunakan mode gelap'" class="flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700">
            <svg x-show="!darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.35 15.35A9 9 0 0 1 8.65 3.65 9 9 0 1 0 20.35 15.35Z"/></svg>
            <svg x-show="darkMode" class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.36 6.36-.7-.7M6.34 6.34l-.7-.7m12.72 0-.7.7M6.34 17.66l-.7.7M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/></svg>
            <span x-text="darkMode ? 'Mode Terang' : 'Mode Gelap'"></span>
        </button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" aria-label="Keluar" class="flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-3H9.75m9 0-3-3m3 3-3 3"/></svg>
                <span>Logout</span>
            </button>
        </form>
    </div>
</nav>
