<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: window.tpuirfTheme.read() }" x-effect="window.tpuirfTheme.apply(darkMode)">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Platform TPUIRF') - TPUIRF</title>
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
<body class="font-inter antialiased min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50 dark:from-gray-900 dark:via-gray-800 dark:to-indigo-950">
    <div class="min-h-screen flex flex-col items-center justify-center p-4 sm:p-6">
        @yield('content')
    </div>
</body>
</html>
