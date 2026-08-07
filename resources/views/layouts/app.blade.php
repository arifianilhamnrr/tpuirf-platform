<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: window.tpuirfTheme.read(), sidebarOpen: false }" x-effect="window.tpuirfTheme.apply(darkMode)" @tpuirf-theme-preview.window="darkMode = Boolean($event.detail.darkMode)">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - TPUIRF</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script>
        window.tpuirfTheme = {
            read() {
                try { return localStorage.getItem('darkMode') === 'true'; }
                catch (_) { return false; }
            },
            apply(darkMode) {
                document.documentElement.classList.toggle('dark', Boolean(darkMode));
                document.documentElement.style.colorScheme = darkMode ? 'dark' : 'light';
                try { localStorage.setItem('darkMode', String(Boolean(darkMode))); }
                catch (_) {}
            },
        };
        window.tpuirfTheme.apply(window.tpuirfTheme.read());
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-inter antialiased bg-gradient-to-br from-indigo-50 via-white to-cyan-50 dark:from-gray-900 dark:via-gray-800 dark:to-indigo-950 min-h-screen">
    <div class="min-h-screen">
        @include('components.navigation')
        <main class="transition-all duration-300 p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                @if (session('success'))
                    <div class="mb-6 p-4 rounded-2xl bg-green-500/10 backdrop-blur-xl border border-green-500/20 text-green-700 dark:text-green-300" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-6 p-4 rounded-2xl bg-red-500/10 backdrop-blur-xl border border-red-500/20 text-red-700 dark:text-red-300" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
