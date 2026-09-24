@props([
    'title'       => null,
    'subtitle'    => null,
    'padding'     => true,
    'hover'       => false,
    'footer'      => null,
])

<div {{ $attributes->merge([
    'class' => 'rounded-xl border border-slate-200 bg-white shadow-sm'
               . ($hover ? ' transition hover:shadow-md hover:border-slate-300' : '')
]) }}>
    {{-- Header --}}
    @if($title || $subtitle || isset($actions))
        <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-slate-100">
            <div>
                @if($title)
                    <h3 class="font-heading text-lg font-bold text-secondary">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="mt-0.5 text-sm text-slate-500">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="shrink-0">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    {{-- Body --}}
    <div @class(['px-6 py-5' => $padding])>
        {{ $slot }}
    </div>

    {{-- Footer --}}
    @isset($footer)
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/60 rounded-b-xl">
            {{ $footer }}
        </div>
    @endisset
</div>