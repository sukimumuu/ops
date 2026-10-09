@props([
    'label' => 'Options',
    'variant' => 'secondary', // primary | secondary | outline | ghost
    'size' => 'md', // sm | md | lg
    'position' => 'bottom-start', // bottom-start | bottom-end | top-start | top-end
    'width' => 'w-52',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg font-heading
             font-semibold tracking-tight outline-none transition cursor-pointer select-none
             focus-visible:ring-2 focus-visible:ring-primary/30';

    $sizes = [
        'sm' => 'px-3.5 py-2 text-xs',
        'md' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];

    $variants = [
        'primary' => 'bg-primary text-white shadow-lg shadow-primary/25 hover:bg-primary-hover',
        'secondary' => 'bg-secondary text-white shadow-lg shadow-secondary/25 hover:bg-slate-700',
        'outline' => 'border-2 border-primary text-primary hover:bg-primary-soft',
        'ghost' => 'text-secondary bg-slate-100 hover:bg-slate-200',
    ];

    $positions = [
        'bottom-start' => 'top-full left-0 mt-2',
        'bottom-end' => 'top-full right-0 mt-2',
        'bottom-middle' => 'top-full left-1/2 transform -translate-x-1/2 mt-2',
        'top-start' => 'bottom-full left-0 mb-2',
        'top-end' => 'bottom-full right-0 mb-2',
        'top-center' => 'bottom-full left-1/2 transform -translate-x-1/2 mb-2',
    ];
@endphp

<div class="relative inline-block" x-data="{
    open: false,
    focusables: [],
    toggle() {
        this.open = !this.open;
        if (this.open) {
            this.$nextTick(() => {
                this.focusables = [...this.$refs.menu.querySelectorAll('[role=menuitem]')];
            });
        }
    },
    close() { this.open = false; },
    focusItem(dir) {
        const idx = this.focusables.indexOf(document.activeElement);
        const next = (idx + dir + this.focusables.length) % this.focusables.length;
        this.focusables[next]?.focus();
    }
}">

    {{-- Trigger button --}}
    <button type="button" @click="toggle()" @keydown.escape="close()"
        @keydown.arrow-down.prevent="if (open) { focusItem(1) } else { toggle(); $nextTick(() => focusItem(1)) }"
        @keydown.arrow-up.prevent="if (open) { focusItem(-1) } else { toggle(); $nextTick(() => focusItem(1)) }"
        :aria-expanded="open" aria-haspopup="menu"
        {{ $attributes->merge(['class' => "$base {$sizes[$size]} {$variants[$variant]}"]) }}>
        {{ $label }}
    </button>

    {{-- Menu --}}
    <div x-ref="menu" x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95" @click.outside="close()"
        @keydown.escape="close(); $refs.button.focus()" role="menu" aria-orientation="vertical" tabindex="-1"
        class="absolute z-50 {{ $positions[$position] }} {{ $width }} p-2
                rounded-xl bg-white border border-slate-200 shadow-lg shadow-slate-900/5">
        <ul class="flex flex-col gap-0.5">
            {{ $slot }}
        </ul>
    </div>
</div>
