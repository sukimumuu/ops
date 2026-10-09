<div class="space-y-8">
    <section class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-primary">Dashboard Seller</p>
            <h1 class="mt-1 font-heading text-2xl font-bold tracking-tight text-secondary sm:text-3xl">
                Selamat datang, {{ $sellerName }}!
            </h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500 sm:text-base">
                Pantau properti, permintaan survei, dan transaksi penjualan Anda dari satu tempat.
            </p>
        </div>
        <a href="{{ route('listing.create') }}"
           class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-primary px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
            </svg>
            Tambah properti
        </a>
    </section>

    <section aria-label="Ringkasan dashboard" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-card-dashboard :padding="false" class="h-full p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Total properti</p>
                    <p class="mt-3 font-heading text-3xl font-bold text-secondary">{{ number_format($sellerStats['totalProperties']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Seluruh listing yang Anda kelola</p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z" />
                    </svg>
                </span>
            </div>
        </x-card-dashboard>

        <x-card-dashboard :padding="false" class="h-full p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Properti tayang</p>
                    <p class="mt-3 font-heading text-3xl font-bold text-secondary">{{ number_format($sellerStats['publishedProperties']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Listing aktif di website</p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                        <circle cx="12" cy="12" r="9" />
                    </svg>
                </span>
            </div>
        </x-card-dashboard>

        <x-card-dashboard :padding="false" class="h-full p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Survei menunggu</p>
                    <p class="mt-3 font-heading text-3xl font-bold text-secondary">{{ number_format($sellerStats['pendingSurveys']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Permintaan yang perlu ditinjau</p>
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
                    <p class="text-sm font-semibold text-slate-500">Transaksi aktif</p>
                    <p class="mt-3 font-heading text-3xl font-bold text-secondary">{{ number_format($sellerStats['activeTransactions']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Sedang dalam proses penjualan</p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M5 3v4m14-4v4M5 11h14v9H5zM8 15h3" />
                    </svg>
                </span>
            </div>
        </x-card-dashboard>
    </section>

    <x-card-dashboard title="Properti terbaru" subtitle="Ringkasan listing yang terakhir Anda tambahkan">
        <x-slot name="actions">
            <a href="{{ route('seller.my-properties') }}" class="text-sm font-semibold text-primary hover:underline">
                Lihat semua
            </a>
        </x-slot>

        <x-table>
            <x-slot name="head">
                <x-table.cell>Nama properti</x-table.cell>
                <x-table.cell>Lokasi</x-table.cell>
                <x-table.cell>Harga</x-table.cell>
                <x-table.cell>Status</x-table.cell>
                <x-table.cell>Tanggal ditambahkan</x-table.cell>
            </x-slot>

            @forelse ($recentProperties as $property)
                <x-table.row striped>
                    <x-table.data>
                        <span class="font-bold text-secondary">{{ $property->title }}</span>
                    </x-table.data>
                    <x-table.data>{{ $property->city }}, {{ $property->province }}</x-table.data>
                    <x-table.data class="font-semibold">Rp {{ number_format((float) $property->price, 0, ',', '.') }}</x-table.data>
                    <x-table.data>
                        <span @class([
                            'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold',
                            'border-slate-200 bg-slate-100 text-slate-700' => $property->status === 'draft',
                            'border-amber-200 bg-amber-50 text-amber-700' => $property->status === 'pending_verification',
                            'border-emerald-200 bg-emerald-50 text-emerald-700' => $property->status === 'published',
                            'border-sky-200 bg-sky-50 text-sky-700' => $property->status === 'reserved',
                            'border-purple-200 bg-purple-50 text-purple-700' => $property->status === 'sold',
                            'border-rose-200 bg-rose-50 text-rose-700' => $property->status === 'archived',
                        ])>
                            @switch($property->status)
                                @case('pending_verification') Menunggu verifikasi @break
                                @case('published') Tayang @break
                                @case('reserved') Dipesan @break
                                @case('sold') Terjual @break
                                @case('archived') Diarsipkan @break
                                @default {{ ucfirst($property->status) }}
                            @endswitch
                        </span>
                    </x-table.data>
                    <x-table.data>{{ $property->created_at->format('d/m/Y') }}</x-table.data>
                </x-table.row>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <p class="font-heading font-bold text-slate-500">Belum ada properti</p>
                        <p class="mt-1 text-sm text-slate-400">Mulai dengan menambahkan listing properti pertama Anda.</p>
                    </td>
                </tr>
            @endforelse
        </x-table>
    </x-card-dashboard>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-card-dashboard title="Permintaan survei terbaru" subtitle="Permintaan baru dari calon pembeli">
            <x-slot name="actions">
                <a href="{{ route('seller.request-survey') }}" class="text-sm font-semibold text-primary hover:underline">
                    Kelola
                </a>
            </x-slot>

            <x-table>
                <x-slot name="head">
                    <x-table.cell>Properti</x-table.cell>
                    <x-table.cell>Pembeli</x-table.cell>
                    <x-table.cell>Jadwal survei</x-table.cell>
                </x-slot>

                @forelse ($recentSurveys as $survey)
                    <x-table.row striped>
                        <x-table.data>
                            <span class="font-bold text-secondary">{{ $survey->property->title }}</span>
                        </x-table.data>
                        <x-table.data>{{ $survey->buyer->name }}</x-table.data>
                        <x-table.data>{{ \Illuminate\Support\Carbon::parse($survey->scheduled_at)->format('d/m/Y H:i') }}</x-table.data>
                    </x-table.row>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center">
                            <p class="font-heading font-bold text-slate-500">Belum ada permintaan survei</p>
                            <p class="mt-1 text-sm text-slate-400">Permintaan survei baru akan muncul di sini.</p>
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </x-card-dashboard>

        <x-card-dashboard title="Transaksi terbaru" subtitle="Pantau perkembangan transaksi penjualan Anda">
            <x-slot name="actions">
                <a href="{{ route('seller.selling-transaction') }}" class="text-sm font-semibold text-primary hover:underline">
                    Lihat semua
                </a>
            </x-slot>

            <x-table>
                <x-slot name="head">
                    <x-table.cell>Properti</x-table.cell>
                    <x-table.cell>Pembeli</x-table.cell>
                    <x-table.cell>Nilai</x-table.cell>
                    <x-table.cell>Status</x-table.cell>
                </x-slot>

                @forelse ($recentTransactions as $transaction)
                    <x-table.row striped>
                        <x-table.data>
                            <span class="font-bold text-secondary">{{ $transaction->property->title }}</span>
                        </x-table.data>
                        <x-table.data>{{ $transaction->buyer->name }}</x-table.data>
                        <x-table.data>Rp {{ number_format((float) $transaction->total_property_amount, 0, ',', '.') }}</x-table.data>
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
                            <p class="mt-1 text-sm text-slate-400">Transaksi penjualan akan tercatat di sini.</p>
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </x-card-dashboard>
    </section>
</div>
