@extends('layouts.app')
@section('page-title', 'Transaksi Penjualan')
@section('content')
    <div class="space-y-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Dashboard seller</p>
                <h1 class="mt-1 font-heading text-2xl font-bold tracking-tight text-secondary">Transaksi penjualan</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">
                    Pantau pembayaran DP, pelunasan escrow, verifikasi BPN, AJB, dan balik nama sertifikat untuk setiap
                    transaksi dari properti yang Anda jual.
                </p>
            </div>

            <a href="{{ route('seller.my-properties') }}"
                class="inline-flex w-fit items-center justify-center gap-2 rounded-lg border-2 border-primary px-4 py-2.5 text-sm font-semibold text-primary transition hover:bg-primary-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z" />
                </svg>
                Properti saya
            </a>
        </header>

        <section aria-label="Ringkasan transaksi penjualan" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-card-dashboard :padding="false" class="h-full p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Total transaksi</p>
                        <p class="mt-3 font-heading text-3xl font-bold text-secondary">{{ number_format($stats['total']) }}
                        </p>
                        <p class="mt-2 text-xs text-slate-500">Seluruh transaksi properti Anda</p>
                    </div>
                    <span class="flex size-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 7h18M5 3v4m14-4v4M5 11h14v9H5zM8 15h3" />
                        </svg>
                    </span>
                </div>
            </x-card-dashboard>

            <x-card-dashboard :padding="false" class="h-full p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Transaksi aktif</p>
                        <p class="mt-3 font-heading text-3xl font-bold text-secondary">{{ number_format($stats['active']) }}
                        </p>
                        <p class="mt-2 text-xs text-slate-500">
                            Nilai aktif Rp {{ number_format($stats['active_value'], 0, ',', '.') }}
                        </p>
                    </div>
                    <span class="flex size-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l2.5 2.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>
                </div>
            </x-card-dashboard>

            <x-card-dashboard :padding="false" class="h-full p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Menunggu pembayaran</p>
                        <p class="mt-3 font-heading text-3xl font-bold text-secondary">
                            {{ number_format($stats['awaiting_payment']) }}</p>
                        <p class="mt-2 text-xs text-slate-500">Belum ada DP yang masuk</p>
                    </div>
                    <span class="flex size-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 7h18v12H3zM7 11h.01M17 15h.01M12 11a2 2 0 1 0 0 4 2 2 0 0 0 0-4ZM6 4h12" />
                        </svg>
                    </span>
                </div>
            </x-card-dashboard>

            <x-card-dashboard :padding="false" class="h-full p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Transaksi selesai</p>
                        <p class="mt-3 font-heading text-3xl font-bold text-secondary">
                            {{ number_format($stats['completed']) }}</p>
                        <p class="mt-2 text-xs text-slate-500">Sertifikat telah dialihkan</p>
                    </div>
                    <span class="flex items-center justify-center size-11 rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                            <circle cx="12" cy="12" r="9" />
                        </svg>
                    </span>
                </div>
            </x-card-dashboard>
        </section>

        <section aria-label="Filter transaksi" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <x-search-filter placeholder="Cari judul properti, alamat, atau nama pembeli...">
                <div>
                    <label for="transaction-state-filter"
                        class="mb-1 block font-sans text-xs font-semibold text-slate-600">Status</label>
                    <select id="transaction-state-filter" name="state" onchange="this.form.submit()"
                        class="min-w-52 rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 font-sans text-sm text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="">Semua status</option>
                        @foreach ($stateOptions as $stateValue => $stateLabel)
                            <option value="{{ $stateValue }}" @selected($state === $stateValue)>{{ $stateLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="transaction-sort"
                        class="mb-1 block font-sans text-xs font-semibold text-slate-600">Urutkan</label>
                    <select id="transaction-sort" name="sort" onchange="this.form.submit()"
                        class="min-w-44 rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 font-sans text-sm text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        @foreach ($sortOptions as $sortValue => $sortLabel)
                            <option value="{{ $sortValue }}" @selected($sort === $sortValue)>{{ $sortLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </x-search-filter>
        </section>

        <x-card-dashboard title="Daftar transaksi penjualan"
            subtitle="Hanya menampilkan transaksi dari properti yang Anda jual">
            @if ($transactions->isEmpty())
                <div class="px-4 py-14 text-center">
                    <div class="mx-auto flex size-20 items-center justify-center rounded-full bg-primary/10 text-secondary"
                        aria-hidden="true">
                        <svg class="size-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 7h16v13H4zM8 11h.01M16 15h.01M12 11a2 2 0 1 0 0 4 2 2 0 0 0 0-4ZM7 4h10" />
                        </svg>
                    </div>

                    @if ($hasFilters)
                        <h2 class="mt-6 font-heading text-lg font-bold text-secondary">Tidak ada transaksi yang cocok</h2>
                        <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">
                            Coba ubah kata kunci atau status yang Anda gunakan untuk menemukan transaksi Anda.
                        </p>
                        <a href="{{ route('seller.selling-transaction') }}"
                            class="mt-6 inline-flex items-center justify-center rounded-lg border-2 border-primary px-5 py-2.5 text-sm font-semibold text-primary transition hover:bg-primary-soft">
                            Reset filter
                        </a>
                    @else
                        <h2 class="mt-6 font-heading text-lg font-bold text-secondary">Belum ada transaksi penjualan</h2>
                        <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">
                            Transaksi akan muncul di sini setelah calon pembeli menyelesaikan survei dan sepakat melanjutkan
                            pembelian properti Anda.
                        </p>
                        <a href="{{ route('seller.request-survey') }}"
                            class="mt-6 inline-flex items-center justify-center rounded-lg border-2 border-primary px-5 py-2.5 text-sm font-semibold text-primary transition hover:bg-primary-soft">
                            Lihat permintaan survei
                        </a>
                    @endif
                </div>
            @else
                <x-table>
                    <x-slot name="head">
                        <x-table.cell>Properti</x-table.cell>
                        <x-table.cell>Pembeli</x-table.cell>
                        <x-table.cell>Nilai transaksi</x-table.cell>
                        <x-table.cell>Progres tahapan</x-table.cell>
                        <x-table.cell>Status</x-table.cell>
                        <x-table.cell>Aksi</x-table.cell>
                    </x-slot>

                    @foreach ($transactions as $transaction)
                        <x-table.row striped>
                            <x-table.data class="align-top">
                                <span class="font-bold text-secondary">{{ $transaction->property->title }}</span>
                                <span class="mt-1 block text-xs font-normal text-slate-500">
                                    {{ $transaction->property->city }}, {{ $transaction->property->province }}
                                </span>
                            </x-table.data>
                            <x-table.data class="align-top">
                                <span class="font-semibold text-secondary">{{ $transaction->buyer->name }}</span>
                                <span
                                    class="mt-1 block text-xs font-normal text-slate-500">{{ $transaction->buyer->email }}</span>
                            </x-table.data>
                            <x-table.data class="align-top">
                                <span class="font-bold text-secondary">
                                    Rp {{ number_format((float) $transaction->total_property_amount, 0, ',', '.') }}
                                </span>
                                <span class="mt-1 block text-xs font-normal text-slate-500">
                                    DP Rp {{ number_format($transaction->confirmed_dp_amount, 0, ',', '.') }}
                                </span>
                                <span class="mt-0.5 block text-xs font-normal text-slate-500">
                                    Sisa Rp {{ number_format($transaction->remaining_amount, 0, ',', '.') }}
                                </span>
                            </x-table.data>
                            <td class="whitespace-normal px-6 py-4 align-top">
                                <div class="min-w-48">
                                    <div
                                        class="flex items-center justify-between gap-3 text-[11px] font-bold text-slate-500">
                                        <span>Tahap {{ $transaction->current_stage }} dari {{ $totalStageCount }}</span>
                                        <span>{{ $transaction->current_stage_progress }}%</span>
                                    </div>
                                    <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-100"
                                        role="progressbar" aria-valuenow="{{ $transaction->current_stage_progress }}"
                                        aria-valuemin="0" aria-valuemax="100"
                                        aria-label="Progres transaksi {{ $transaction->property->title }}">
                                        <div class="h-full rounded-full bg-primary"
                                            style="width: {{ $transaction->current_stage_progress }}%"></div>
                                    </div>
                                    <p class="mt-2 text-xs font-semibold text-secondary">
                                        {{ $transaction->current_stage_label }}
                                    </p>
                                    <p class="mt-1 text-xs font-normal leading-5 text-slate-500">
                                        {{ $transaction->next_action }}
                                    </p>

                                    @if ($transaction->escrow_release_status === \App\Models\EscrowDisbursement::STATUS_RELEASED)
                                        <span
                                            class="mt-2 inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700">
                                            Dana escrow telah dicairkan
                                        </span>
                                    @elseif ($transaction->escrow_release_status === \App\Models\EscrowDisbursement::STATUS_PENDING)
                                        <span
                                            class="mt-2 inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-800">
                                            Menunggu konfirmasi pencairan escrow
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <x-table.data class="align-top">
                                <x-transaction-status-badge :status="$transaction->state" />
                            </x-table.data>
                            <x-table.data class="align-top">
                                <a href="{{ route('seller.my-properties.show', ['property' => $transaction->property->uuid]) }}"
                                    class="inline-flex items-center gap-1.5 rounded font-sans text-sm font-semibold text-primary transition hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:ring-offset-2">
                                    Lihat detail
                                    <span aria-hidden="true">&rarr;</span>
                                </a>
                            </x-table.data>
                        </x-table.row>
                    @endforeach
                </x-table>

                <x-table.pagination :paginator="$transactions" class="mt-4" />
            @endif
        </x-card-dashboard>
    </div>
@endsection
