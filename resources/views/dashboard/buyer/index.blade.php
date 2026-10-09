<div class="space-y-8">
    <section class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-primary">Dashboard Pembeli</p>
            <h1 class="mt-1 font-heading text-2xl font-bold tracking-tight text-secondary sm:text-3xl">
                Selamat datang, {{ $buyerName }}!
            </h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500 sm:text-base">
                Pantau jadwal survei dan proses transaksi properti Anda dengan mudah.
            </p>
        </div>
        <a href="{{ route('properties') }}"
           class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-primary px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.35-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
            </svg>
            Jelajahi properti
        </a>
    </section>

    <section aria-label="Ringkasan aktivitas pembeli" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-card-dashboard :padding="false" class="h-full p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Total survei</p>
                    <p class="mt-3 font-heading text-3xl font-bold text-secondary">{{ number_format($buyerStats['totalSurveys']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Seluruh permintaan dan jadwal survei</p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v13H4V6a1 1 0 0 1 1-1Zm3 10h3" />
                    </svg>
                </span>
            </div>
        </x-card-dashboard>

        <x-card-dashboard :padding="false" class="h-full p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Survei mendatang</p>
                    <p class="mt-3 font-heading text-3xl font-bold text-secondary">{{ number_format($buyerStats['upcomingSurveys']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Jadwal yang perlu Anda persiapkan</p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 2.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
            </div>
        </x-card-dashboard>

        <x-card-dashboard :padding="false" class="h-full p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Transaksi berjalan</p>
                    <p class="mt-3 font-heading text-3xl font-bold text-secondary">{{ number_format($buyerStats['activeTransactions']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Pembelian yang masih diproses</p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M5 3v4m14-4v4M5 11h14v9H5zM8 15h3" />
                    </svg>
                </span>
            </div>
        </x-card-dashboard>

        <x-card-dashboard :padding="false" class="h-full p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Transaksi selesai</p>
                    <p class="mt-3 font-heading text-3xl font-bold text-secondary">{{ number_format($buyerStats['completedTransactions']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Pembelian yang telah diselesaikan</p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                        <circle cx="12" cy="12" r="9" />
                    </svg>
                </span>
            </div>
        </x-card-dashboard>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-card-dashboard title="Jadwal survei mendatang" subtitle="Survei properti yang akan datang atau menunggu konfirmasi">
            <x-slot name="actions">
                <a href="{{ route('buyer.survey-schedule') }}" class="text-sm font-semibold text-primary hover:underline">
                    Lihat semua
                </a>
            </x-slot>

            <x-table>
                <x-slot name="head">
                    <x-table.cell>Properti</x-table.cell>
                    <x-table.cell>Penjual</x-table.cell>
                    <x-table.cell>Jadwal</x-table.cell>
                    <x-table.cell>Status</x-table.cell>
                </x-slot>

                @forelse ($upcomingSurveys as $survey)
                    <x-table.row striped>
                        <x-table.data>
                            <span class="font-bold text-secondary">{{ $survey->property->title }}</span>
                            <span class="mt-1 block text-xs text-slate-500">{{ $survey->property->city }}, {{ $survey->property->province }}</span>
                        </x-table.data>
                        <x-table.data>{{ $survey->seller->name }}</x-table.data>
                        <x-table.data>{{ \Illuminate\Support\Carbon::parse($survey->scheduled_at)->format('d/m/Y H:i') }}</x-table.data>
                        <x-table.data>
                            <span @class([
                                'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold',
                                'border-amber-200 bg-amber-50 text-amber-700' => $survey->status === 'requested',
                                'border-emerald-200 bg-emerald-50 text-emerald-700' => $survey->status === 'confirmed',
                            ])>
                                {{ $survey->status === 'confirmed' ? 'Dikonfirmasi' : 'Menunggu konfirmasi' }}
                            </span>
                        </x-table.data>
                    </x-table.row>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center">
                            <p class="font-heading font-bold text-slate-500">Belum ada jadwal survei mendatang</p>
                            <p class="mt-1 text-sm text-slate-400">Jadwal survei berikutnya akan tampil di sini.</p>
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </x-card-dashboard>

        <x-card-dashboard title="Transaksi terbaru" subtitle="Pantau tahapan pembelian dan status escrow">
            <x-slot name="actions">
                <a href="{{ route('buyer.transaction-escrow') }}" class="text-sm font-semibold text-primary hover:underline">
                    Lihat semua
                </a>
            </x-slot>

            <x-table>
                <x-slot name="head">
                    <x-table.cell>Properti</x-table.cell>
                    <x-table.cell>Penjual</x-table.cell>
                    <x-table.cell>Nilai properti</x-table.cell>
                    <x-table.cell>Status</x-table.cell>
                </x-slot>

                @forelse ($recentTransactions as $transaction)
                    <x-table.row striped>
                        <x-table.data>
                            <span class="font-bold text-secondary">{{ $transaction->property->title }}</span>
                            <span class="mt-1 block text-xs text-slate-500">{{ $transaction->property->city }}, {{ $transaction->property->province }}</span>
                        </x-table.data>
                        <x-table.data>{{ $transaction->seller->name }}</x-table.data>
                        <x-table.data>
                            <span class="font-semibold">Rp {{ number_format((float) $transaction->total_property_amount, 0, ',', '.') }}</span>
                            <span class="mt-1 block text-xs text-slate-500">Booking fee Rp {{ number_format((float) $transaction->booking_fee_amount, 0, ',', '.') }}</span>
                        </x-table.data>
                        <x-table.data>
                            <span @class([
                                'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold',
                                'border-slate-200 bg-slate-100 text-slate-700' => $transaction->state === 'draft',
                                'border-amber-200 bg-amber-50 text-amber-700' => in_array($transaction->state, ['held_in_escrow', 'bpn_checking', 'ajb_scheduled']),
                                'border-sky-200 bg-sky-50 text-sky-700' => $transaction->state === 'bpn_cleared',
                                'border-emerald-200 bg-emerald-50 text-emerald-700' => $transaction->state === 'completed',
                                'border-rose-200 bg-rose-50 text-rose-700' => $transaction->state === 'refunded',
                            ])>
                                @switch($transaction->state)
                                    @case('held_in_escrow') Dalam escrow @break
                                    @case('bpn_checking') Verifikasi BPN @break
                                    @case('bpn_cleared') BPN selesai @break
                                    @case('ajb_scheduled') Jadwal AJB @break
                                    @case('completed') Selesai @break
                                    @case('refunded') Dikembalikan @break
                                    @default Menunggu pembayaran
                                @endswitch
                            </span>
                        </x-table.data>
                    </x-table.row>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center">
                            <p class="font-heading font-bold text-slate-500">Belum ada transaksi</p>
                            <p class="mt-1 text-sm text-slate-400">Transaksi pembelian akan tercatat di sini.</p>
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </x-card-dashboard>
    </section>
</div>
