@php
    $auditStats = [
        [
            'label' => 'Aktivitas hari ini',
            'value' => $summary['total'],
            'description' => 'Total jejak audit yang tercatat hari ini.',
        ],
        [
            'label' => 'Autentikasi',
            'value' => $summary['authentication'],
            'description' => 'Masuk, keluar, dan percobaan masuk gagal.',
        ],
        [
            'label' => 'Transaksi',
            'value' => $summary['transaction'],
            'description' => 'DP, pelunasan escrow, dan pencairan dana.',
        ],
        [
            'label' => 'Moderasi properti',
            'value' => $summary['moderation'],
            'description' => 'Pengajuan, persetujuan, dan penolakan listing.',
        ],
    ];
@endphp

<section aria-label="Ringkasan audit hari ini" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($auditStats as $stat)
        <x-card-dashboard :title="$stat['label']" class="h-full">
            <p class="font-heading text-3xl font-bold tabular-nums text-secondary">
                {{ number_format($stat['value'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $stat['description'] }}</p>
        </x-card-dashboard>
    @endforeach
</section>
