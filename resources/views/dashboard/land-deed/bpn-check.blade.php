@extends('layouts.app')
@section('page-title', 'Pengecekan BPN')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <x-ppat.header
        breadcrumb="Operasional kantor / Pengecekan BPN"
        date="Jumat, 16 Oktober 2026"
        title="Pengecekan BPN"
        subtitle="Kelola pengajuan pengecekan sertifikat dan tindak lanjut hasil dari Kantor Pertanahan."
        actionLabel="+ Tambah Pengajuan"
        exportLabel="Ekspor"
    />

    {{-- 4 Stat Cards --}}
    <section aria-label="Ringkasan Pengecekan BPN" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ppat.stat-card
            :title="$metrics['waiting']['title']"
            :count="$metrics['waiting']['count']"
            :unit="$metrics['waiting']['unit']"
            :description="$metrics['waiting']['description']"
            :descriptionClass="$metrics['waiting']['descriptionClass']"
        >
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </x-slot:icon>
        </x-ppat.stat-card>

        <x-ppat.stat-card
            :title="$metrics['in_progress']['title']"
            :count="$metrics['in_progress']['count']"
            :unit="$metrics['in_progress']['unit']"
            :description="$metrics['in_progress']['description']"
            :descriptionClass="$metrics['in_progress']['descriptionClass']"
        >
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </x-slot:icon>
        </x-ppat.stat-card>

        <x-ppat.stat-card
            :title="$metrics['needs_repair']['title']"
            :count="$metrics['needs_repair']['count']"
            :unit="$metrics['needs_repair']['unit']"
            :description="$metrics['needs_repair']['description']"
            :descriptionClass="$metrics['needs_repair']['descriptionClass']"
        >
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </x-slot:icon>
        </x-ppat.stat-card>

        <x-ppat.stat-card
            :title="$metrics['completed']['title']"
            :count="$metrics['completed']['count']"
            :unit="$metrics['completed']['unit']"
            :description="$metrics['completed']['description']"
            :descriptionClass="$metrics['completed']['descriptionClass']"
        >
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </x-slot:icon>
        </x-ppat.stat-card>
    </section>

    {{-- Info banner --}}
    <x-ppat.alert-banner type="info">
        <span class="font-medium text-blue-950">6 berkas belum selesai.</span> 1 berkas memerlukan perbaikan dokumen. Tindak lanjuti TRX-2026-0150 sebelum pengajuan ulang.
    </x-ppat.alert-banner>

    {{-- Main Table Card --}}
    <div class="rounded-xl border border-slate-200/90 bg-white shadow-xs overflow-hidden">
        {{-- Card Header --}}
        <div class="p-5 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-secondary">Daftar pengecekan sertifikat</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">24 berkas</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">24 pengajuan tercatat • dahulukan berkas yang memerlukan tindak lanjut</p>
            </div>
            <div class="flex items-center gap-1.5 text-xs font-medium text-slate-600 self-start sm:self-auto">
                <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0-3.75-3.75M17.25 21 21 17.25" />
                </svg>
                <span>Belum selesai lebih dulu</span>
            </div>
        </div>

        {{-- Filters toolbar --}}
        <div class="p-4 border-b border-slate-200/80 bg-slate-50/50 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1">
                {{-- Search --}}
                <div class="relative flex-1 sm:max-w-xs">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input type="text" placeholder="Cari transaksi, klien, atau nomor SHM..." class="w-full rounded-lg border border-slate-200 bg-white py-1.5 pl-9 pr-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                </div>

                {{-- Status Filter --}}
                <div class="relative">
                    <select class="w-full appearance-none rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs text-slate-600 hover:border-slate-300 focus:border-primary focus:outline-none cursor-pointer">
                        <option>Semua status</option>
                        <option>Menunggu</option>
                        <option>Dalam Proses</option>
                        <option>Perlu Perbaikan</option>
                        <option>Selesai</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>

                {{-- Officer Filter --}}
                <div class="relative">
                    <select class="w-full appearance-none rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs text-slate-600 hover:border-slate-300 focus:border-primary focus:outline-none cursor-pointer">
                        <option>Semua petugas</option>
                        <option>Nabila Putri</option>
                        <option>Rizal Firmansyah</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>

                {{-- Date/Month Filter --}}
                <div class="relative">
                    <select class="w-full appearance-none rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs text-slate-600 hover:border-slate-300 focus:border-primary focus:outline-none cursor-pointer">
                        <option>Oktober 2026</option>
                        <option>September 2026</option>
                        <option>Agustus 2026</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Atur kolom --}}
            <button type="button" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 shadow-xs cursor-pointer">
                <svg class="size-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                </svg>
                <span>Atur kolom</span>
            </button>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-semibold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th scope="col" class="py-3.5 pl-5 pr-3">Transaksi</th>
                        <th scope="col" class="px-3 py-3.5">Nama Klien</th>
                        <th scope="col" class="px-3 py-3.5">Objek / Referensi Sertifikat</th>
                        <th scope="col" class="px-3 py-3.5">Tanggal Pengajuan</th>
                        <th scope="col" class="px-3 py-3.5">Status Pengecekan</th>
                        <th scope="col" class="px-3 py-3.5">Tindak Lanjut / Hasil</th>
                        <th scope="col" class="py-3.5 pl-3 pr-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                    @foreach($items as $row)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-4 pl-5 pr-3 font-semibold text-secondary whitespace-nowrap">
                                <div>{{ $row['trx_id'] }}</div>
                                <div class="text-[11px] font-normal text-slate-400 mt-0.5">{{ $row['trx_type'] }}</div>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <div class="font-semibold text-secondary">{{ $row['client'] }}</div>
                            </td>
                            <td class="px-3 py-4">
                                <div class="font-medium text-secondary">{{ $row['cert_title'] }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $row['cert_desc'] }}</div>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <div class="font-medium text-secondary">{{ $row['date'] }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $row['time'] }}</div>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                @if($row['status_variant'] === 'amber')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200/70">
                                        {{ $row['status'] }}
                                    </span>
                                @elseif($row['status_variant'] === 'blue')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200/70">
                                        {{ $row['status'] }}
                                    </span>
                                @elseif($row['status_variant'] === 'rose')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200/70">
                                        {{ $row['status'] }}
                                    </span>
                                @elseif($row['status_variant'] === 'emerald')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                                        {{ $row['status'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-4">
                                <div class="font-medium text-secondary">{{ $row['follow_up'] }}</div>
                                <div class="text-[11px] {{ $row['follow_up_sub_class'] }} mt-0.5">{{ $row['follow_up_sub'] }}</div>
                            </td>
                            <td class="py-4 pl-3 pr-5 text-right whitespace-nowrap">
                                <button type="button" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-secondary shadow-xs cursor-pointer">
                                    {{ $row['action'] }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination footer --}}
        <div class="p-4 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span>Menampilkan 1-6 dari 24 berkas</span>
                <div class="relative">
                    <select class="appearance-none rounded-md border border-slate-200 bg-white py-1 pl-2.5 pr-7 text-xs text-slate-600 focus:outline-none cursor-pointer">
                        <option>6 / halaman</option>
                        <option>10 / halaman</option>
                        <option>25 / halaman</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-1.5 text-slate-400">
                        <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-1">
                <button type="button" class="rounded-md border border-slate-200 px-2.5 py-1 text-slate-600 hover:bg-slate-50 cursor-pointer">Sebelumnya</button>
                <button type="button" class="rounded-md bg-slate-100 font-bold px-3 py-1 text-secondary">1</button>
                <button type="button" class="rounded-md px-3 py-1 text-slate-600 hover:bg-slate-50 cursor-pointer">2</button>
                <button type="button" class="rounded-md px-3 py-1 text-slate-600 hover:bg-slate-50 cursor-pointer">3</button>
                <button type="button" class="rounded-md px-3 py-1 text-slate-600 hover:bg-slate-50 cursor-pointer">4</button>
                <button type="button" class="rounded-md border border-slate-200 px-2.5 py-1 text-slate-600 hover:bg-slate-50 cursor-pointer">Berikutnya</button>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <x-ppat.footer />
</div>
@endsection
