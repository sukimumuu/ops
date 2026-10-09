<div class="space-y-8">
    <header class="flex flex-col gap-2 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-primary">Superadmin</p>
            <h1 class="mt-1 font-heading text-2xl font-bold text-secondary">Ringkasan platform</h1>
            <p class="mt-1 text-sm text-slate-500">Pantau pengguna, properti, dan proses transaksi.</p>
        </div>
        <p class="text-sm text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</p>
    </header>

    <section aria-label="Ringkasan metrik" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($metrics as $metric)
            <x-card-dashboard :title="$metric['label']" :subtitle="$metric['description']" class="h-full">
                <x-slot name="actions">
                    <span class="inline-flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                            aria-hidden="true">
                            @switch($metric['icon'])
                                @case('users')
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.87M14 3.13a4 4 0 0 1 0 7.75M14 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" />
                                @break

                                @case('home')
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-6v-6h-4v6H4a1 1 0 0 1-1-1V10Z" />
                                @break

                                @case('clipboard')
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 5h6m-6 4h6m-6 4h3m-8 7h14a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1h-3.18a3 3 0 0 0-5.64 0H4a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1Z" />
                                @break

                                @case('arrows')
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 7h13m0 0-3-3m3 3-3 3M17 17H4m0 0 3 3m-3-3 3-3" />
                                @break
                            @endswitch
                        </svg>
                    </span>
                </x-slot>
                <p class="font-heading text-3xl font-bold tabular-nums text-secondary">
                    {{ number_format($metric['value'], 0, ',', '.') }}</p>
            </x-card-dashboard>
        @endforeach
    </section>

    <section aria-labelledby="recent-transactions-heading" class="space-y-4">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 id="recent-transactions-heading" class="font-heading text-lg font-bold text-secondary">Transaksi
                    terbaru</h2>
                <p class="mt-1 text-sm text-slate-500">Pergerakan terbaru pada alur escrow dan AJB.</p>
            </div>
            <a href="{{ route('superadmin.transaction-and-escrow') }}"
                class="shrink-0 text-sm font-semibold text-primary hover:text-secondary">
                Semua transaksi <span aria-hidden="true">&rarr;</span>
            </a>
        </div>

        <x-table>
            <x-slot name="head">
                <x-table.cell>Properti</x-table.cell>
                <x-table.cell>Pihak</x-table.cell>
                <x-table.cell>Nilai properti</x-table.cell>
                <x-table.cell>Status</x-table.cell>
                <x-table.cell>Ditambahkan</x-table.cell>
            </x-slot>
            @forelse ($recentTransactions as $transaction)
                @php
                    $transactionStateLabels = [
                        'draft' => 'Draft',
                        'held_in_escrow' => 'Dana di escrow',
                        'bpn_checking' => 'Verifikasi BPN',
                        'bpn_cleared' => 'BPN selesai',
                        'ajb_scheduled' => 'AJB terjadwal',
                        'completed' => 'Selesai',
                        'refunded' => 'Dana dikembalikan',
                    ];
                    $transactionStateClasses = [
                        'draft' => 'bg-slate-100 text-slate-700',
                        'held_in_escrow' => 'bg-sky-100 text-sky-800',
                        'bpn_checking' => 'bg-amber-100 text-amber-800',
                        'bpn_cleared' => 'bg-emerald-100 text-emerald-800',
                        'ajb_scheduled' => 'bg-orange-100 text-orange-800',
                        'completed' => 'bg-emerald-100 text-emerald-800',
                        'refunded' => 'bg-rose-100 text-rose-800',
                    ];
                @endphp
                <x-table.row striped>
                    <x-table.data>
                        <p class="max-w-56 truncate font-semibold text-secondary">{{ $transaction->property->title }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">{{ strtoupper(substr($transaction->uuid, 0, 8)) }}</p>
                    </x-table.data>
                    <x-table.data>
                        <p>{{ $transaction->buyer->name }}</p>
                        <p class="mt-1 text-xs text-slate-500">Penjual: {{ $transaction->seller->name }}</p>
                    </x-table.data>
                    <x-table.data class="tabular-nums">
                        Rp {{ number_format((float) $transaction->total_property_amount, 0, ',', '.') }}
                    </x-table.data>
                    <x-table.data>
                        <span @class([
                            'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                            $transactionStateClasses[$transaction->state] ??
                            'bg-slate-100 text-slate-700',
                        ])>
                            {{ $transactionStateLabels[$transaction->state] ?? $transaction->state }}
                        </span>
                    </x-table.data>
                    <x-table.data>{{ $transaction->created_at->format('d M Y') }}</x-table.data>
                </x-table.row>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada transaksi.</td>
                </tr>
            @endforelse
        </x-table>
    </section>

    <section aria-labelledby="pending-properties-heading" class="space-y-4">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 id="pending-properties-heading" class="font-heading text-lg font-bold text-secondary">Properti
                    menunggu verifikasi</h2>
                <p class="mt-1 text-sm text-slate-500">Listing terbaru yang masih perlu ditinjau.</p>
            </div>
            <a href="{{ route('superadmin.master-data-property') }}"
                class="shrink-0 text-sm font-semibold text-primary hover:text-secondary">
                Data properti <span aria-hidden="true">&rarr;</span>
            </a>
        </div>

        <x-table>
            <x-slot name="head">
                <x-table.cell>Properti</x-table.cell>
                <x-table.cell>Penjual</x-table.cell>
                <x-table.cell>Lokasi</x-table.cell>
                <x-table.cell>Status</x-table.cell>
                <x-table.cell>Diajukan</x-table.cell>
            </x-slot>
            @forelse ($pendingProperties as $property)
                <x-table.row striped>
                    <x-table.data>
                        <p class="max-w-56 truncate font-semibold text-secondary">{{ $property->title }}</p>
                    </x-table.data>
                    <x-table.data>{{ $property->seller->name }}</x-table.data>
                    <x-table.data>{{ $property->city }}</x-table.data>
                    <x-table.data>
                        <span
                            class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Menunggu</span>
                    </x-table.data>
                    <x-table.data>{{ $property->created_at->format('d M Y') }}</x-table.data>
                </x-table.row>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Tidak ada properti yang
                        menunggu verifikasi.</td>
                </tr>
            @endforelse
        </x-table>
    </section>
</div>
