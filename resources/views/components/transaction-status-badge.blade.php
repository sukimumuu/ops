@props(['status', 'label' => null])

@php
    $styles = match ($status) {
        'draft' => [
            'label' => 'Menunggu pembayaran DP',
            'badge' => 'border-slate-200 bg-slate-100 text-slate-700',
            'dot' => 'bg-slate-500',
        ],
        'dp_paid' => [
            'label' => 'DP dibayar, dana ditahan',
            'badge' => 'border-amber-200 bg-amber-50 text-amber-800',
            'dot' => 'bg-amber-500',
        ],
        'held_in_escrow' => [
            'label' => 'Dalam escrow',
            'badge' => 'border-amber-200 bg-amber-50 text-amber-800',
            'dot' => 'bg-amber-500',
        ],
        'bpn_checking' => [
            'label' => 'Verifikasi BPN',
            'badge' => 'border-sky-200 bg-sky-50 text-sky-700',
            'dot' => 'bg-sky-500',
        ],
        'bpn_cleared' => [
            'label' => 'BPN selesai',
            'badge' => 'border-sky-200 bg-sky-50 text-sky-700',
            'dot' => 'bg-sky-500',
        ],
        'ajb_scheduled' => [
            'label' => 'Jadwal AJB',
            'badge' => 'border-violet-200 bg-violet-50 text-violet-700',
            'dot' => 'bg-violet-500',
        ],
        'certificate_transfer_pending' => [
            'label' => 'Menunggu balik nama sertifikat',
            'badge' => 'border-violet-200 bg-violet-50 text-violet-700',
            'dot' => 'bg-violet-500',
        ],
        'completed' => [
            'label' => 'Selesai',
            'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'dot' => 'bg-emerald-500',
        ],
        'refunded' => [
            'label' => 'Dikembalikan',
            'badge' => 'border-rose-200 bg-rose-50 text-rose-700',
            'dot' => 'bg-rose-500',
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
