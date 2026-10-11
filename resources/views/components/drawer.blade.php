@props([
    'title' => null,
    'subtitle' => null,
    'width' => 'lg:w-1/2', // 50% on desktop; override e.g. 'lg:w-[45%]' or 'lg:w-2/5'
    'persistent' => false, // true = no backdrop click-to-close
])

<div x-data="{ open: false }" @keydown.escape.window="if (open) { open = false }" {{ $attributes }}>

    {{-- Trigger slot --}}
    <span @click="open = true">{{ $trigger }}</span>

    {{-- Backdrop --}}
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-hidden">

            <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @click="if (!{{ $persistent ? 'true' : 'false' }}) open = false"
                class="absolute inset-0 bg-secondary/50"></div>

            {{-- Panel --}}
            <div role="dialog" aria-modal="true" x-show="open" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="absolute inset-y-0 right-0 w-full sm:w-2/3 {{ $width }}
                        flex flex-col bg-white shadow-2xl">

                {{-- Header --}}
                @if ($title || isset($headerActions))
                    <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-slate-200 shrink-0">
                        <div>
                            @if ($title)
                                <h2 class="font-heading text-lg font-bold text-secondary">{{ $title }}</h2>
                            @endif
                            @if ($subtitle)
                                <p class="mt-0.5 text-sm text-slate-500">{{ $subtitle }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @isset($headerActions)
                                {{ $headerActions }}
                            @endisset
                            <button @click="open = false" aria-label="Close panel"
                                class="p-2 rounded-lg text-slate-400 hover:text-secondary hover:bg-slate-100 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Body --}}
                <div class="flex-1 overflow-y-auto px-6 py-5">
                    {{ $slot }}
                </div>

                {{-- Footer --}}
                @isset($footer)
                    <div class="shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50/60">
                        {{ $footer }}
                    </div>
                @endisset
            </div>
        </div>
    </template>
</div>
