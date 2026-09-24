@props([
    'variant'   => 'primary',       // primary | secondary | outline | ghost | danger
    'size'      => 'md',            // sm | md | lg
    'type'      => 'button',
    'disabled'  => false,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg font-heading
             font-semibold tracking-tight outline-none transition
             focus-visible:ring-2 focus-visible:ring-primary/30
             disabled:cursor-not-allowed disabled:opacity-50';

    $sizes = [
        'sm' => 'px-3.5 py-2 text-xs',
        'md' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];

    $variants = [
        'primary'   => 'bg-primary text-white shadow-lg shadow-primary/25 hover:bg-primary-hover',
        'secondary' => 'bg-secondary text-white shadow-lg shadow-secondary/25 hover:bg-slate-700',
        'outline'   => 'border-2 border-primary text-primary hover:bg-primary-soft',
        'ghost'     => 'text-secondary hover:bg-slate-100',
        'danger'    => 'bg-red-600 text-white shadow-lg shadow-red-600/25 hover:bg-red-700',
    ];
@endphp

<button type="{{ $type }}" @disabled($disabled)
        {{ $attributes->merge(['class' => "$base {$sizes[$size]} {$variants[$variant]}"]) }}>
    {{ $slot }}
</button>