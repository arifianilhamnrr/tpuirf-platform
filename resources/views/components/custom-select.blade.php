@props(['name', 'options' => [], 'selected' => '', 'placeholder' => 'Pilih opsi'])

<div x-data="{ 
        open: false, 
        dropUp: false,
        value: '{{ $selected }}',
        options: {{ json_encode($options) }},
        get selectedLabel() {
            return this.options[this.value] || '{{ $placeholder }}';
        },
        toggle() {
            this.open = !this.open;
            if (this.open) this.$nextTick(() => this.updateDirection());
        },
        updateDirection() {
            const trigger = this.$refs.trigger.getBoundingClientRect();
            const menuHeight = Math.min(this.$refs.menu.scrollHeight, 240);
            const spaceBelow = window.innerHeight - trigger.bottom;
            const spaceAbove = trigger.top;
            this.dropUp = spaceBelow < menuHeight + 16 && spaceAbove > spaceBelow;
        }
    }" 
    @click.away="open = false" 
    @resize.window="if (open) updateDirection()"
    @scroll.window="if (open) updateDirection()"
    class="relative">
    
    <input type="hidden" name="{{ $name }}" x-model="value" required>
    
    <button type="button"
            x-ref="trigger"
            @click="toggle()" 
            :aria-expanded="open"
            class="w-full flex items-center justify-between px-4 py-3 rounded-2xl bg-white/50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 shadow-sm hover:bg-white/80 dark:hover:bg-gray-700/80">
        <span x-text="selectedLabel" :class="!value ? 'text-gray-400 dark:text-gray-400' : ''"></span>
        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    
    <div x-show="open"
         x-ref="menu"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         :class="dropUp ? 'bottom-full mb-2 origin-bottom' : 'top-full mt-2 origin-top'"
         class="absolute z-50 w-full bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl overflow-hidden"
         style="display: none;">
        <ul class="max-h-60 overflow-auto p-2 space-y-1">
            <template x-for="(label, val) in options" :key="val">
                <li>
                    <button type="button" 
                            @click="value = val; open = false"
                            class="w-full text-left px-4 py-2.5 rounded-xl transition-all duration-200 flex items-center justify-between"
                            :class="value === val ? 'bg-indigo-500 text-white font-medium shadow-md shadow-indigo-500/20' : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30'">
                        <span x-text="label"></span>
                        <svg x-show="value === val" class="w-5 h-5" :class="value === val ? 'text-white' : 'text-indigo-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </li>
            </template>
        </ul>
    </div>
</div>
