@props([
    'title',
    'count',
    'unit' => 'berkas',
    'description' => '',
    'descriptionClass' => 'text-slate-500',
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200/90 bg-white p-5 flex flex-col justify-between shadow-xs transition-shadow hover:shadow-sm']) }}>
    <div class="flex items-start justify-between gap-2">
        <p class="text-sm font-medium text-slate-600">{{ $title }}</p>
        @isset($icon)
            <span class="text-slate-400 shrink-0">{{ $icon }}</span>
        @endisset
    </div>
    <div class="mt-4">
        <div class="flex items-baseline gap-1.5">
            <span class="text-3xl font-bold tracking-tight text-secondary">{{ $count }}</span>
            <span class="text-sm text-slate-500">{{ $unit }}</span>
        </div>
        @if($description)
            <p class="text-xs {{ $descriptionClass }} mt-2 leading-relaxed">{{ $description }}</p>
        @endif
    </div>
</div>
