<div {{ $attributes->merge(['class' => 'backdrop-blur-xl bg-white/70 dark:bg-gray-800/50 rounded-3xl p-6 border border-white/20 dark:border-gray-700/30 shadow-xl hover:shadow-2xl transition-all duration-300']) }}>
    {{ $slot }}
</div>
