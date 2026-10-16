@extends('layouts.app')
@section('page-title', 'Jadwal Penandatanganan AJB')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <x-ppat.header
        breadcrumb="Operasional kantor / Jadwal Penandatanganan AJB"
        date="Jumat, 16 Oktober 2026"
        title="Jadwal Penandatanganan AJB"
        subtitle="Atur pertemuan para pihak, kesiapan penandatanganan, dan ketersediaan PPAT."
        actionLabel="+ Tambah Jadwal"
        exportLabel="Ekspor"
    />

    {{-- 4 Stat Cards --}}
    <section aria-label="Ringkasan Jadwal AJB" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ppat.stat-card
            :title="$metrics['upcoming']['title']"
            :count="$metrics['upcoming']['count']"
            :unit="$metrics['upcoming']['unit']"
            :description="$metrics['upcoming']['description']"
            :descriptionClass="$metrics['upcoming']['descriptionClass']"
        >
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
            </x-slot:icon>
        </x-ppat.stat-card>

        <x-ppat.stat-card
            :title="$metrics['today']['title']"
            :count="$metrics['today']['count']"
            :unit="$metrics['today']['unit']"
            :description="$metrics['today']['description']"
            :descriptionClass="$metrics['today']['descriptionClass']"
        >
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9 3.75h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                </svg>
            </x-slot:icon>
        </x-ppat.stat-card>

        <x-ppat.stat-card
            :title="$metrics['confirmed']['title']"
            :count="$metrics['confirmed']['count']"
            :unit="$metrics['confirmed']['unit']"
            :description="$metrics['confirmed']['description']"
            :descriptionClass="$metrics['confirmed']['descriptionClass']"
        >
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </x-slot:icon>
        </x-ppat.stat-card>

        <x-ppat.stat-card
            :title="$metrics['conflicts']['title']"
            :count="$metrics['conflicts']['count']"
            :unit="$metrics['conflicts']['unit']"
            :description="$metrics['conflicts']['description']"
            :descriptionClass="$metrics['conflicts']['descriptionClass']"
        >
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </x-slot:icon>
        </x-ppat.stat-card>
    </section>

    {{-- Conflict Alert Banner --}}
    <x-ppat.alert-banner type="warning">
        <span class="font-medium">Konflik hari ini, 10.30–11.00 WIB.</span> TRX-2026-0142 dan TRX-2026-0143 memakai PPAT Andini Prameswari dan Ruang Akad yang sama. Atur ulang salah satu jadwal.
    </x-ppat.alert-banner>

    {{-- Main Table Card --}}
    <div class="rounded-xl border border-slate-200/90 bg-white shadow-xs overflow-hidden">
        {{-- Card Header --}}
        <div class="p-5 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-secondary">Agenda mendatang</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">7 janji</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">7 janji • 16-20 Oktober 2026 • seluruh waktu dalam WIB</p>
            </div>
            <div class="flex items-center gap-1.5 text-xs font-medium text-slate-600 self-start sm:self-auto">
                <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0-3.75-3.75M17.25 21 21 17.25" />
                </svg>
                <span>Waktu terdekat lebih dulu</span>
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
                    <input type="text" placeholder="Cari pihak atau nomor transaksi..." class="w-full rounded-lg border border-slate-200 bg-white py-1.5 pl-9 pr-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                </div>

                {{-- Date Range Filter --}}
                <div class="relative">
                    <select class="w-full appearance-none rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs text-slate-600 hover:border-slate-300 focus:border-primary focus:outline-none cursor-pointer">
                        <option>16–20 Okt 2026</option>
                        <option>Hari ini (16 Okt)</option>
                        <option>Minggu ini</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="relative">
                    <select class="w-full appearance-none rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs text-slate-600 hover:border-slate-300 focus:border-primary focus:outline-none cursor-pointer">
                        <option>Semua status</option>
                        <option>Terkonfirmasi</option>
                        <option>Menunggu Konfirmasi</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>

                {{-- PPAT Filter --}}
                <div class="relative">
                    <select class="w-full appearance-none rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs text-slate-600 hover:border-slate-300 focus:border-primary focus:outline-none cursor-pointer">
                        <option>Andini Prameswari</option>
                        <option>Semua PPAT</option>
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
                        <th scope="col" class="py-3.5 pl-5 pr-3">Tanggal</th>
                        <th scope="col" class="px-3 py-3.5">Waktu / Lokasi</th>
                        <th scope="col" class="px-3 py-3.5">Para Pihak</th>
                        <th scope="col" class="px-3 py-3.5">Transaksi / Properti</th>
                        <th scope="col" class="px-3 py-3.5">PPAT / Kesiapan</th>
                        <th scope="col" class="px-3 py-3.5">Status Janji</th>
                        <th scope="col" class="py-3.5 pl-3 pr-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                    @foreach($items as $row)
                        <tr class="hover:bg-slate-50/60 transition-colors {{ $row['conflict'] ? 'bg-rose-50/30' : '' }}">
                            <td class="py-4 pl-5 pr-3 font-semibold text-secondary whitespace-nowrap">
                                <div>{{ $row['date'] }}</div>
                                <div class="text-[11px] font-normal text-slate-400 mt-0.5">{{ $row['date_sub'] }}</div>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <div class="font-semibold text-secondary">{{ $row['time'] }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $row['location'] }}</div>
                            </td>
                            <td class="px-3 py-4">
                                <div class="font-semibold text-secondary whitespace-nowrap">{{ $row['parties'] }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $row['parties_role'] }}</div>
                            </td>
                            <td class="px-3 py-4">
                                <div class="font-medium text-secondary whitespace-nowrap">{{ $row['trx_id'] }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 whitespace-nowrap">{{ $row['property'] }}</div>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <div class="font-medium text-secondary">{{ $row['ppat'] }}</div>
                                <div class="text-[11px] {{ $row['readiness_class'] }} mt-0.5">{{ $row['readiness'] }}</div>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                @if($row['status_variant'] === 'emerald')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                                        {{ $row['status'] }}
                                    </span>
                                @elseif($row['status_variant'] === 'rose')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200/70">
                                        {{ $row['status'] }}
                                    </span>
                                @elseif($row['status_variant'] === 'amber')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200/70">
                                        {{ $row['status'] }}
                                    </span>
                                @endif

                                @if($row['conflict'])
                                    <div class="text-[11px] text-rose-600 font-medium mt-1">{{ $row['conflict'] }}</div>
                                @endif
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
                <span>Menampilkan 1-7 dari 7 janji</span>
                <div class="relative">
                    <select class="appearance-none rounded-md border border-slate-200 bg-white py-1 pl-2.5 pr-7 text-xs text-slate-600 focus:outline-none cursor-pointer">
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
                <button type="button" class="rounded-md border border-slate-200 px-2.5 py-1 text-slate-600 hover:bg-slate-50 cursor-pointer">Berikutnya</button>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <x-ppat.footer />
</div>
@endsection
