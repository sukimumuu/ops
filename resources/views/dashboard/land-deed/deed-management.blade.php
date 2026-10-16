@extends('layouts.app')
@section('page-title', 'Manajemen Akta')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <x-ppat.header
        breadcrumb="Operasional kantor / Manajemen Akta"
        date="Jumat, 16 Oktober 2026"
        title="Manajemen Akta"
        subtitle="Kelola penyusunan, penandatanganan, dan arsip akta dalam satu daftar kerja kantor."
        actionLabel="+ Buat Draft Akta"
        exportLabel="Ekspor"
    />

    {{-- 4 Stat Cards --}}
    <section aria-label="Ringkasan Manajemen Akta" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ppat.stat-card
            :title="$metrics['draft']['title']"
            :count="$metrics['draft']['count']"
            :unit="$metrics['draft']['unit']"
            :description="$metrics['draft']['description']"
            :descriptionClass="$metrics['draft']['descriptionClass']"
        >
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
            </x-slot:icon>
        </x-ppat.stat-card>

        <x-ppat.stat-card
            :title="$metrics['drafting']['title']"
            :count="$metrics['drafting']['count']"
            :unit="$metrics['drafting']['unit']"
            :description="$metrics['drafting']['description']"
            :descriptionClass="$metrics['drafting']['descriptionClass']"
        >
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </x-slot:icon>
        </x-ppat.stat-card>

        <x-ppat.stat-card
            :title="$metrics['waiting_sign']['title']"
            :count="$metrics['waiting_sign']['count']"
            :unit="$metrics['waiting_sign']['unit']"
            :description="$metrics['waiting_sign']['description']"
            :descriptionClass="$metrics['waiting_sign']['descriptionClass']"
        >
            <x-slot:icon>
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                </svg>
            </x-slot:icon>
        </x-ppat.stat-card>

        <x-ppat.stat-card
            :title="$metrics['signed']['title']"
            :count="$metrics['signed']['count']"
            :unit="$metrics['signed']['unit']"
            :description="$metrics['signed']['description']"
            :descriptionClass="$metrics['signed']['descriptionClass']"
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
        Status di sini menunjukkan proses administrasi internal kantor, bukan penetapan status hukum akta atau hak atas tanah. Nomor draft adalah referensi internal, bukan nomor akta resmi.
    </x-ppat.alert-banner>

    {{-- Main Table Card --}}
    <div class="rounded-xl border border-slate-200/90 bg-white shadow-xs overflow-hidden">
        {{-- Card Header --}}
        <div class="p-5 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-secondary">Daftar akta</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">13 berkas</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">13 berkas akta • nomor akta resmi ditampilkan pada berkas yang telah ditandatangani</p>
            </div>
            <div class="flex items-center gap-1.5 text-xs font-medium text-slate-600 self-start sm:self-auto">
                <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0-3.75-3.75M17.25 21 21 17.25" />
                </svg>
                <span>Urutan prioritas kantor</span>
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
                    <input type="text" placeholder="Cari nomor akta, draft, atau nama pihak..." class="w-full rounded-lg border border-slate-200 bg-white py-1.5 pl-9 pr-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                </div>

                {{-- Status Filter --}}
                <div class="relative">
                    <select class="w-full appearance-none rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs text-slate-600 hover:border-slate-300 focus:border-primary focus:outline-none cursor-pointer">
                        <option>Semua status</option>
                        <option>Draft</option>
                        <option>Dalam Penyusunan</option>
                        <option>Menunggu Tanda Tangan</option>
                        <option>Ditandatangani</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>

                {{-- Deed Type Filter --}}
                <div class="relative">
                    <select class="w-full appearance-none rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs text-slate-600 hover:border-slate-300 focus:border-primary focus:outline-none cursor-pointer">
                        <option>Semua jenis akta</option>
                        <option>AJB</option>
                        <option>APHT</option>
                        <option>Akta Hibah</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>

                {{-- Month Filter --}}
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
                        <th scope="col" class="py-3.5 pl-5 pr-3">Nomor / Jenis Akta</th>
                        <th scope="col" class="px-3 py-3.5">Klien / Para Pihak</th>
                        <th scope="col" class="px-3 py-3.5">Objek Tanah/Properti</th>
                        <th scope="col" class="px-3 py-3.5">Tanggal Dibuat</th>
                        <th scope="col" class="px-3 py-3.5">Status Administrasi</th>
                        <th scope="col" class="px-3 py-3.5">Petugas / Catatan</th>
                        <th scope="col" class="py-3.5 pl-3 pr-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                    @foreach($items as $row)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-4 pl-5 pr-3 font-semibold text-secondary whitespace-nowrap">
                                <div>{{ $row['code'] }}</div>
                                <div class="text-[11px] font-normal text-slate-400 mt-0.5">{{ $row['type_desc'] }}</div>
                            </td>
                            <td class="px-3 py-4">
                                <div class="font-semibold text-secondary whitespace-nowrap">{{ $row['parties'] }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $row['parties_role'] }}</div>
                            </td>
                            <td class="px-3 py-4">
                                <div class="font-medium text-secondary whitespace-nowrap">{{ $row['property'] }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 whitespace-nowrap">{{ $row['property_sub'] }}</div>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap font-medium text-secondary">
                                {{ $row['created_date'] }}
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                @if($row['status_variant'] === 'slate')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $row['status'] }}
                                    </span>
                                @elseif($row['status_variant'] === 'blue')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200/70">
                                        {{ $row['status'] }}
                                    </span>
                                @elseif($row['status_variant'] === 'amber')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200/70">
                                        {{ $row['status'] }}
                                    </span>
                                @elseif($row['status_variant'] === 'emerald')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                                        {{ $row['status'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap">
                                <div class="font-medium text-secondary">{{ $row['officer'] }}</div>
                                <div class="text-[11px] {{ $row['officer_note_class'] }} mt-0.5">{{ $row['officer_note'] }}</div>
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
                <span>Menampilkan 1-6 dari 13 berkas</span>
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
                <button type="button" class="rounded-md border border-slate-200 px-2.5 py-1 text-slate-600 hover:bg-slate-50 cursor-pointer">Berikutnya</button>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <x-ppat.footer />
</div>
@endsection
