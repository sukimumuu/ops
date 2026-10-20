@props([
    'status',
    'label' => null,
])

@php
    $styles = match ($status) {
        'requested' => [
            'label' => 'Menunggu persetujuan',
            'badge' => 'border-amber-200 bg-amber-50 text-amber-800',
            'dot' => 'bg-amber-500',
        ],
        'confirmed' => [
            'label' => 'Disetujui',
            'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'dot' => 'bg-emerald-500',
        ],
        'rejected' => [
            'label' => 'Ditolak',
            'badge' => 'border-rose-200 bg-rose-50 text-rose-700',
            'dot' => 'bg-rose-500',
        ],
        'completed' => [
            'label' => 'Selesai',
            'badge' => 'border-sky-200 bg-sky-50 text-sky-700',
            'dot' => 'bg-sky-500',
        ],
        'cancelled' => [
            'label' => 'Dibatalkan',
            'badge' => 'border-slate-200 bg-slate-50 text-slate-600',
            'dot' => 'bg-slate-400',
        ],
        default => [
            'label' => ucfirst((string) $status),
            'badge' => 'border-slate-200 bg-slate-50 text-slate-600',
            'dot' => 'bg-slate-400',
        ],
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold', $styles['badge']]) }}>
    <span class="size-1.5 rounded-full {{ $styles['dot'] }}" aria-hidden="true"></span>
    {{ $label ?? $styles['label'] }}
</span>
