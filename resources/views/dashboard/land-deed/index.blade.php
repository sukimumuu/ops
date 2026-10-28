@extends('layouts.app')
@section('page-title', 'Jadwal Survei')

@section('content')
    <div class="space-y-5">
        <header class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Operasional kantor</p>
                <h1 class="mt-1 text-2xl font-bold text-secondary">Dashboard antrian</h1>
                <p class="mt-1 text-sm text-slate-500">Pantau jadwal survei pada properti yang ditugaskan kepada Anda.</p>
            </div>
        </header>

        @if (session('status'))
            <div role="status"
                class="flex items-start gap-2.5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                <svg class="mt-0.5 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->has('status'))
            <div role="alert"
                class="flex items-start gap-2.5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                <svg class="mt-0.5 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ $errors->first('status') }}</span>
            </div>
        @endif

        <x-survey-calendar
            :month="$month"
            :calendar-days="$calendarDays"
            :appointments-by-date="$appointmentsByDate"
            :bookings="$calendarBookings"
            :appointments="$appointments"
            perspective="ppat"
            reset-route="land-deed.index"
            title="Kalender jadwal survei"
            description="Pilih avatar calon pembeli untuk membuka rincian survei pada properti yang ditugaskan kepada Anda."
        />
    </div>

    @if (false)
        <div class="space-y-6">
            {{-- Header --}}
            <x-ppat.header breadcrumb="Operasional kantor / Dashboard Antrian" date="Jumat, 16 Oktober 2026"
                title="Dashboard Antrian"
                subtitle="Pantau alur berkas klien, dari pengecekan sertifikat hingga administrasi transaksi selesai."
                actionLabel="+ Tambah Antrian" exportLabel="Ekspor" />

            {{-- 5 Summary Stat Cards --}}
            <section aria-label="Ringkasan Antrian" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <x-ppat.stat-card :title="$metrics['total']['title']" :count="$metrics['total']['count']" :unit="$metrics['total']['unit']" :description="$metrics['total']['description']" :descriptionClass="$metrics['total']['descriptionClass']">
                    <x-slot:icon>
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                        </svg>
                    </x-slot:icon>
                </x-ppat.stat-card>

                <x-ppat.stat-card :title="$metrics['waiting_check']['title']" :count="$metrics['waiting_check']['count']" :unit="$metrics['waiting_check']['unit']" :description="$metrics['waiting_check']['description']" :descriptionClass="$metrics['waiting_check']['descriptionClass']">
                    <x-slot:icon>
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </x-slot:icon>
                </x-ppat.stat-card>

                <x-ppat.stat-card :title="$metrics['waiting_tax']['title']" :count="$metrics['waiting_tax']['count']" :unit="$metrics['waiting_tax']['unit']" :description="$metrics['waiting_tax']['description']"
                    :descriptionClass="$metrics['waiting_tax']['descriptionClass']">
                    <x-slot:icon>
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                    </x-slot:icon>
                </x-ppat.stat-card>

                <x-ppat.stat-card :title="$metrics['waiting_sign']['title']" :count="$metrics['waiting_sign']['count']" :unit="$metrics['waiting_sign']['unit']" :description="$metrics['waiting_sign']['description']"
                    :descriptionClass="$metrics['waiting_sign']['descriptionClass']">
                    <x-slot:icon>
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </x-slot:icon>
                </x-ppat.stat-card>

                <x-ppat.stat-card :title="$metrics['completed']['title']" :count="$metrics['completed']['count']" :unit="$metrics['completed']['unit']" :description="$metrics['completed']['description']"
                    :descriptionClass="$metrics['completed']['descriptionClass']">
                    <x-slot:icon>
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </x-slot:icon>
                </x-ppat.stat-card>
            </section>

            {{-- Main Table Card --}}
            <div class="rounded-xl border border-slate-200/90 bg-white shadow-xs overflow-hidden">
                {{-- Card Header --}}
                <div
                    class="p-5 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-secondary">Daftar antrian</h2>
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">24
                                berkas</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Seluruh antrian kantor • status menunjukkan tahapan
                            administrasi saat ini</p>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs font-medium text-slate-600 self-start sm:self-auto">
                        <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0-3.75-3.75M17.25 21 21 17.25" />
                        </svg>
                        <span>Urutan prioritas kantor</span>
                    </div>
                </div>

                {{-- Filters toolbar --}}
                <div
                    class="p-4 border-b border-slate-200/80 bg-slate-50/50 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1">
                        {{-- Search --}}
                        <div class="relative flex-1 sm:max-w-xs">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </div>
                            <input type="text" placeholder="Cari nomor antrian, transaksi, atau klien..."
                                class="w-full rounded-lg border border-slate-200 bg-white py-1.5 pl-9 pr-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        </div>

                        {{-- Status Filter --}}
                        <div class="relative">
                            <select
                                class="w-full appearance-none rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs text-slate-600 hover:border-slate-300 focus:border-primary focus:outline-none cursor-pointer">
                                <option>Semua status</option>
                                <option>Menunggu Pengecekan</option>
                                <option>Menunggu Validasi Pajak</option>
                                <option>Menunggu Penandatanganan</option>
                                <option>Transaksi Selesai</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>

                        {{-- Transaction Type Filter --}}
                        <div class="relative">
                            <select
                                class="w-full appearance-none rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs text-slate-600 hover:border-slate-300 focus:border-primary focus:outline-none cursor-pointer">
                                <option>Semua transaksi</option>
                                <option>Jual beli</option>
                                <option>Hibah</option>
                                <option>Waris</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>

                        {{-- Date Filter --}}
                        <div class="relative">
                            <select
                                class="w-full appearance-none rounded-lg border border-slate-200 bg-white py-1.5 pl-3 pr-8 text-xs text-slate-600 hover:border-slate-300 focus:border-primary focus:outline-none cursor-pointer">
                                <option>Semua tanggal</option>
                                <option>Hari ini</option>
                                <option>7 hari terakhir</option>
                                <option>Bulan ini</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Atur kolom --}}
                    <button type="button"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 shadow-xs cursor-pointer">
                        <svg class="size-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                        </svg>
                        <span>Atur kolom</span>
                    </button>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-semibold text-slate-600 uppercase tracking-wider">
                            <tr>
                                <th scope="col" class="py-3.5 pl-5 pr-3">Nomor Antrian</th>
                                <th scope="col" class="px-3 py-3.5">Nama Klien</th>
                                <th scope="col" class="px-3 py-3.5">Jenis Transaksi</th>
                                <th scope="col" class="px-3 py-3.5">Objek Tanah/Properti</th>
                                <th scope="col" class="px-3 py-3.5">Tanggal Masuk</th>
                                <th scope="col" class="px-3 py-3.5">Status</th>
                                <th scope="col" class="py-3.5 pl-3 pr-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                            @foreach ($queueItems as $row)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-4 pl-5 pr-3 font-semibold text-secondary whitespace-nowrap">
                                        <div>{{ $row['queue_no'] }}</div>
                                        <div class="text-[11px] font-normal text-slate-400 mt-0.5">{{ $row['trx_id'] }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <div class="font-semibold text-secondary">{{ $row['client'] }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $row['client_type'] }}</div>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap font-medium text-slate-800">
                                        {{ $row['trx_type'] }}
                                    </td>
                                    <td class="px-3 py-4">
                                        <div class="font-medium text-secondary">{{ $row['property_title'] }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $row['property_desc'] }}</div>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <div class="font-medium text-secondary">{{ $row['date'] }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $row['time'] }}</div>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        @if ($row['status_variant'] === 'amber')
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200/70">
                                                {{ $row['status'] }}
                                            </span>
                                        @elseif($row['status_variant'] === 'blue')
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200/70">
                                                {{ $row['status'] }}
                                            </span>
                                        @elseif($row['status_variant'] === 'purple')
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200/70">
                                                {{ $row['status'] }}
                                            </span>
                                        @elseif($row['status_variant'] === 'emerald')
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                                                {{ $row['status'] }}
                                            </span>
                                        @endif

                                        @if ($row['note'])
                                            <div
                                                class="text-[11px] mt-1 {{ str_contains($row['note'], 'Perbaikan') ? 'text-rose-600 font-medium' : 'text-slate-500' }}">
                                                {{ $row['note'] }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-4 pl-3 pr-5 text-right whitespace-nowrap">
                                        <button type="button"
                                            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-secondary shadow-xs cursor-pointer">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination footer --}}
                <div
                    class="p-4 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                    <div class="flex items-center gap-2">
                        <span>Menampilkan 1-6 dari 24 berkas</span>
                        <div class="relative">
                            <select
                                class="appearance-none rounded-md border border-slate-200 bg-white py-1 pl-2.5 pr-7 text-xs text-slate-600 focus:outline-none cursor-pointer">
                                <option>6 / halaman</option>
                                <option>10 / halaman</option>
                                <option>25 / halaman</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-1.5 text-slate-400">
                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1">
                        <button type="button"
                            class="rounded-md border border-slate-200 px-2.5 py-1 text-slate-600 hover:bg-slate-50 cursor-pointer">Sebelumnya</button>
                        <button type="button"
                            class="rounded-md bg-slate-100 font-bold px-3 py-1 text-secondary">1</button>
                        <button type="button"
                            class="rounded-md px-3 py-1 text-slate-600 hover:bg-slate-50 cursor-pointer">2</button>
                        <button type="button"
                            class="rounded-md px-3 py-1 text-slate-600 hover:bg-slate-50 cursor-pointer">3</button>
                        <button type="button"
                            class="rounded-md px-3 py-1 text-slate-600 hover:bg-slate-50 cursor-pointer">4</button>
                        <button type="button"
                            class="rounded-md border border-slate-200 px-2.5 py-1 text-slate-600 hover:bg-slate-50 cursor-pointer">Berikutnya</button>
                    </div>
                </div>
            </div>

            {{-- Bottom Two Cards: Perlu ditindaklanjuti & Aktivitas terbaru --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Card 1 --}}
                <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs">
                    <h3 class="text-sm font-semibold text-secondary mb-3">Perlu ditindaklanjuti</h3>
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2.5">
                            <span
                                class="inline-flex size-7 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </span>
                            <span class="font-medium text-slate-800">Dewi Lestari · unggah ulang hasil pindai SHM</span>
                        </div>
                        <span class="text-slate-400">Hari ini</span>
                    </div>
                </div>

                {{-- Card 2 --}}
                <div class="rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs">
                    <h3 class="text-sm font-semibold text-secondary mb-3">Aktivitas terbaru</h3>
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2.5">
                            <span
                                class="inline-flex size-7 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </span>
                            <span class="font-medium text-slate-800">Antrian Rina Kartika ditambahkan oleh Nabila</span>
                        </div>
                        <span class="text-slate-400">08.15 WIB</span>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <x-ppat.footer />
        </div>
    @endif
@endsection
