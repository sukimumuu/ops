@props([
    'status',
    'label' => null,
])

@php
    $styles = match ($status) {
        'draft' => [
            'label' => 'Draft',
            'badge' => 'border-slate-200 bg-slate-100 text-slate-700',
            'dot' => 'bg-slate-500',
        ],
        'pending_verification' => [
            'label' => 'Menunggu Verifikasi',
            'badge' => 'border-amber-200 bg-amber-50 text-amber-700',
            'dot' => 'bg-amber-500',
        ],
        'published' => [
            'label' => 'Terbit',
            'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'dot' => 'bg-emerald-500',
        ],
        'reserved' => [
            'label' => 'Dipesan',
            'badge' => 'border-sky-200 bg-sky-50 text-sky-700',
            'dot' => 'bg-sky-500',
        ],
        'sold' => [
            'label' => 'Terjual',
            'badge' => 'border-purple-200 bg-purple-50 text-purple-700',
            'dot' => 'bg-purple-500',
        ],
        'archived' => [
            'label' => 'Diarsipkan',
            'badge' => 'border-rose-200 bg-rose-50 text-rose-700',
            'dot' => 'bg-rose-500',
        ],
        'rejected' => [
            'label' => 'Ditolak',
            'badge' => 'border-red-200 bg-red-50 text-red-700',
            'dot' => 'bg-red-500',
        ],
        default => [
            'label' => ucfirst((string) $status),
            'badge' => 'border-slate-200 bg-slate-50 text-slate-600',
            'dot' => 'bg-slate-400',
        ],
    };
@endphp

<span
    {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold', $styles['badge']]) }}>
    <span class="size-1.5 rounded-full {{ $styles['dot'] }}" aria-hidden="true"></span>
    {{ $label ?? $styles['label'] }}
</span>
