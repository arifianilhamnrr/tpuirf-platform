@php
    $adminTabs = [
        ['route' => 'admin.dashboard', 'label' => 'Ringkasan'],
        ['route' => 'admin.researchers', 'label' => 'Peneliti'],
        ['route' => 'admin.respondents', 'label' => 'Responden'],
        ['route' => 'admin.experiments', 'label' => 'Eksperimen'],
        ['route' => 'admin.templates', 'label' => 'Template UI'],
        ['route' => 'admin.logs', 'label' => 'Audit Log'],
        ['route' => 'admin.configuration', 'label' => 'Konfigurasi'],
    ];
@endphp

<nav class="flex gap-2 overflow-x-auto rounded-2xl border border-white/20 bg-white/60 p-2 shadow-lg backdrop-blur-xl dark:border-gray-700/30 dark:bg-gray-800/40" aria-label="Menu administrasi">
    @foreach($adminTabs as $tab)
        <a href="{{ route($tab['route']) }}" class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold transition {{ request()->routeIs($tab['route']) ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-600 hover:bg-indigo-500/10 hover:text-indigo-700 dark:text-gray-300 dark:hover:text-indigo-300' }}">{{ $tab['label'] }}</a>
    @endforeach
</nav>
