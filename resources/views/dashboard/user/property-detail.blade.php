@extends('layouts.app')
@section('page-title', 'Detail Properti Dibeli')
@section('content')
    @php
        $propertyFacts = array_filter(
            [
                'Tipe properti' => $property->property_type === 'tanah' ? 'Tanah' : 'Bangunan',
                'Jenis' => filled($property->type) ? ucfirst((string) $property->type) : null,
                'Kategori' => $property->category === 'rent' ? 'Disewakan' : 'Dijual',
                'Sertifikat' => $property->certificate_type,
                'Luas tanah' => filled($property->land_area_sqm)
                    ? number_format((float) $property->land_area_sqm, 0, ',', '.') . ' m²'
                    : null,
                'Luas bangunan' => filled($property->building_area_sqm)
                    ? number_format((float) $property->building_area_sqm, 0, ',', '.') . ' m²'
                    : null,
                'Kamar tidur' => filled($property->rooms) ? $property->rooms . ' KT' : null,
                'Kamar mandi' => filled($property->bathrooms) ? $property->bathrooms . ' KM' : null,
                'Lantai' => $property->floors,
                'Kondisi' => $property->condition,
                'Lebar depan' => filled($property->front_width)
                    ? number_format((float) $property->front_width, 0, ',', '.') . ' m'
                    : null,
                'Kontur tanah' => $property->land_contour,
                'Akses jalan' => $property->road_access,
                'Zona' => $property->zone_type,
                'Penjual' => $property->seller?->name,
                'PPAT' => $property->assignedPpat?->name,
            ],
            fn($value): bool => filled($value),
        );

        $progressTone = match ($progress['tone']) {
            'amber' => [
                'bar' => 'bg-amber-500',
                'badge' => 'border-amber-200 bg-amber-50 text-amber-700',
            ],
            'sky' => [
                'bar' => 'bg-sky-500',
                'badge' => 'border-sky-200 bg-sky-50 text-sky-700',
            ],
            'emerald' => [
                'bar' => 'bg-emerald-500',
                'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            ],
            'rose' => [
                'bar' => 'bg-rose-500',
                'badge' => 'border-rose-200 bg-rose-50 text-rose-700',
            ],
            default => [
                'bar' => 'bg-slate-400',
                'badge' => 'border-slate-200 bg-slate-50 text-slate-600',
            ],
        };

        $progressSteps = ['Pengajuan survei', 'Konfirmasi jadwal', 'Survei selesai'];
        $rentPeriod =
            $property->category === 'rent'
                ? match ($property->rent_period) {
                    'bulan' => '/ bulan',
                    'tahun' => '/ tahun',
                    'minggu' => '/ minggu',
                    'hari' => '/ hari',
                    default => null,
                }
                : null;
    @endphp

    <div class="mx-auto w-full max-w-7xl space-y-6 font-sans text-slate-700">
        <div>
            <nav aria-label="Breadcrumb"
                class="mb-3 flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500 sm:text-sm">
                <a href="{{ route('home') }}"
                    class="rounded transition hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                    Beranda
                </a>
                <span aria-hidden="true" class="text-slate-300">&gt;</span>
                <a href="{{ route('buyer.bought-properties') }}"
                    class="rounded transition hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                    Properti Dibeli
                </a>
                <span aria-hidden="true" class="text-slate-300">&gt;</span>
                <span aria-current="page" class="font-semibold text-slate-600">Detail properti</span>
            </nav>

            <header class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-property-status-badge :status="$property->status" />
                        <span
                            class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-600">
                            {{ $property->category === 'rent' ? 'Disewakan' : 'Dijual' }}
                        </span>
                        @if ($property->negotiable)
                            <span
                                class="inline-flex items-center rounded-full border border-primary/20 bg-primary/5 px-2.5 py-1 text-xs font-bold text-primary">
                                Dapat dinegosiasi
                            </span>
                        @endif
                    </div>

                    <h1 class="mt-3 font-heading text-2xl font-bold tracking-tight text-secondary sm:text-3xl">
                        {{ $property->title }}
                    </h1>

                    <p class="mt-2 flex items-start gap-1.5 text-sm text-slate-500">
                        <svg class="mt-0.5 size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11Z" />
                            <circle cx="12" cy="10" r="2.5" />
                        </svg>
                        {{ collect([$property->district, $property->city, $property->province])->filter()->implode(', ') }}
                    </p>

                    <p class="mt-2 text-xs text-slate-400">
                        Ditambahkan {{ $property->created_at->locale('id')->translatedFormat('d F Y') }}
                        &middot; ID {{ $property->uuid }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('buyer.bought-properties') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7" />
                        </svg>
                        Kembali
                    </a>

                    @if ($property->status === 'published')
                        <a href="{{ route('property-details', ['uuid' => $property->uuid]) }}" target="_blank"
                            rel="noopener"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                            Lihat properti publik
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5h5v5m0-5-9 9M19 14v5H5V5h5" />
                            </svg>
                        </a>
                    @endif
                </div>
            </header>
        </div>

        @if (session('status'))
            <div role="status"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(20rem,1fr)]">
            <div class="space-y-6">
                <x-card-dashboard :padding="false" class="overflow-hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)]">
                        <div class="relative min-h-64 overflow-hidden bg-slate-100 sm:min-h-72 lg:min-h-full">
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-slate-400"
                                aria-hidden="true">
                                <svg class="size-10" fill="none" stroke="currentColor" stroke-width="1.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 10.5 12 3l9 7.5M5 9.5V21h5v-6h4v6h5V9.5" />
                                </svg>
                                <span class="text-xs font-medium">Foto belum tersedia</span>
                            </div>

                            @if (filled($property->image_url))
                                <img src="{{ $property->image_url }}" alt="Foto properti: {{ $property->title }}"
                                    class="absolute inset-0 h-full w-full object-cover" onerror="this.remove()">
                            @endif

                            @if ($property->photos->isNotEmpty())
                                <span
                                    class="absolute left-4 top-4 z-10 inline-flex items-center rounded-full bg-white/95 px-3 py-1 text-[11px] font-bold tracking-wide text-secondary shadow-sm backdrop-blur">
                                    {{ $property->photos->count() }} foto
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-col p-5 sm:p-6">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-primary">Harga properti</p>
                            <div class="mt-2 flex flex-wrap items-baseline gap-x-2 gap-y-1">
                                <p class="font-heading text-2xl font-bold tracking-tight text-secondary sm:text-3xl">
                                    Rp {{ number_format((float) $property->price, 0, ',', '.') }}
                                </p>
                                @if ($rentPeriod)
                                    <span class="text-sm font-medium text-slate-500">{{ $rentPeriod }}</span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $property->negotiable ? 'Harga dapat dinegosiasi dengan penjual.' : 'Harga sesuai yang tertera pada listing.' }}
                            </p>

                            <dl class="mt-5 grid grid-cols-2 gap-3">
                                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                                    <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Luas tanah
                                    </dt>
                                    <dd class="mt-1 text-sm font-bold text-secondary">
                                        {{ number_format((float) $property->land_area_sqm, 0, ',', '.') }} m²
                                    </dd>
                                </div>
                                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                                    <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Luas bangunan
                                    </dt>
                                    <dd class="mt-1 text-sm font-bold text-secondary">
                                        {{ filled($property->building_area_sqm) ? number_format((float) $property->building_area_sqm, 0, ',', '.') . ' m²' : '-' }}
                                    </dd>
                                </div>
                                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                                    <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Kamar tidur
                                    </dt>
                                    <dd class="mt-1 text-sm font-bold text-secondary">
                                        {{ filled($property->rooms) ? $property->rooms . ' KT' : '-' }}
                                    </dd>
                                </div>
                                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                                    <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Kamar mandi
                                    </dt>
                                    <dd class="mt-1 text-sm font-bold text-secondary">
                                        {{ filled($property->bathrooms) ? $property->bathrooms . ' KM' : '-' }}
                                    </dd>
                                </div>
                            </dl>

                            <a href="{{ $mapUrl }}" target="_blank" rel="noopener"
                                class="mt-auto inline-flex items-center gap-1.5 pt-5 text-sm font-semibold text-primary transition hover:text-primary/80">
                                Buka lokasi di peta
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14 5h5v5m0-5-9 9M19 14v5H5V5h5" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </x-card-dashboard>

                <x-card-dashboard title="Deskripsi properti"
                    subtitle="Informasi detail mengenai spesifikasi dan kondisi properti">
                    <p class="whitespace-pre-line text-sm leading-7 text-slate-600">
                        {{ filled($property->description) ? $property->description : 'Belum ada deskripsi properti.' }}
                    </p>
                </x-card-dashboard>

                <x-card-dashboard id="survey" title="Survei & Tahapan Transaksi"
                    subtitle="{{ $selectedAppointment?->buyer_intent === \App\Models\Appointment::BUYER_INTENT_DEAL ? 'Tahapan transaksi setelah berminat melanjutkan pembelian' : 'Hasil dan progres survei untuk properti ini' }}">
                    <x-slot name="actions">
                        <a href="{{ route('buyer.survey-schedule') }}"
                            class="text-sm font-semibold text-primary hover:underline">
                            Kalender survei
                        </a>
                    </x-slot>

                    @if ($selectedAppointment?->buyer_intent === \App\Models\Appointment::BUYER_INTENT_DEAL)
                        @php
                            $deedTimelineSteps = [
                                [
                                    'title' => 'Jadwal Temu PPAT',
                                    'icon' =>
                                        'M8 3v3m8-3v3M4 8h16M5 5h14a1 1 0 0 1 1 1v13H4V6a1 1 0 0 1 1-1Zm2 7h3m-3 4h3m3-4h3',
                                ],
                                [
                                    'title' => 'Pembayaran DP (Escrow)',
                                    'icon' =>
                                        'M3 7h18v12H3zM7 11h.01M17 15h.01M12 11a2 2 0 1 0 0 4 2 2 0 0 0 0-4ZM6 4h12',
                                ],
                                [
                                    'title' => 'Pengecekan Legalitas BPN & Validasi Pajak',
                                    'icon' => 'M6 3h8l4 4v5m-4-9v5h5m-13-5v18h6m-2-12h5m-5 4h4m4 5 2 2 4-4',
                                ],
                                [
                                    'title' => 'Pelunasan Properti (Escrow)',
                                    'icon' =>
                                        'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                                ],
                                [
                                    'title' => 'Penjadwalan & TTD Akta Jual Beli (Offline)',
                                    'icon' => 'm4 16.5-.5 4 4-.5L19 8.5 15.5 5 4 16.5Zm9.5-9.5 4 4M14 4l3-3 4 4-3 3',
                                ],
                                [
                                    'title' => 'Eksekusi Pencairan Escrow (Release Funds)',
                                    'icon' => 'M3 8h18M5 8v9m4-9v9m6-9v9m4-9v9M3 19h18M12 3l9 4H3l9-4Zm-8 18h16',
                                ],
                                [
                                    'title' => 'Balik Nama Sertifikat (Tahap Akhir)',
                                    'icon' => 'M7 3h10v13H7zM10 7h4m-4 3h4m-2 5v6l2-1.5L16 21v-6',
                                ],
                            ];
                        @endphp

                        <div class="space-y-5">
                            <div
                                class="flex flex-col gap-3 rounded-xl border border-emerald-100 bg-emerald-50/70 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-heading font-bold text-secondary">
                                        Proses Pembelian Sedang Berjalan
                                    </p>
                                    <p class="mt-1 text-xs text-emerald-700">
                                        Keputusan Anda: <span class="font-bold">Berminat membeli</span>
                                    </p>
                                </div>
                                @if ($selectedAppointment->completed_at)
                                    <p class="text-xs font-medium text-emerald-700">
                                        Survei selesai
                                        {{ $selectedAppointment->completed_at->locale('id')->translatedFormat('d F Y') }}
                                    </p>
                                @endif
                            </div>

                            <div class="relative">
                                <span aria-hidden="true"
                                    class="absolute bottom-5 left-5 top-5 w-px bg-slate-200 sm:left-1/2 sm:-translate-x-1/2"></span>

                                <ol class="space-y-4 sm:space-y-5">
                                    @foreach ($deedTimelineSteps as $index => $step)
                                        <li
                                            class="relative grid grid-cols-[2.5rem_minmax(0,1fr)] items-start gap-x-3 sm:grid-cols-[minmax(0,1fr)_2.5rem_minmax(0,1fr)] sm:gap-x-4">

                                            <div @class([
                                                'col-start-2 row-start-1 rounded-xl border p-3 sm:p-4',
                                                'border-primary/20 bg-primary/5 sm:col-start-1' => $index === 0,
                                                'border-slate-200 bg-white sm:col-start-3' =>
                                                    $index !== 0 && $index % 2 === 1,
                                                'border-slate-200 bg-white sm:col-start-1' =>
                                                    $index !== 0 && $index % 2 === 0,
                                            ])>
                                                <div @class([
                                                    'flex items-start gap-3',
                                                    'sm:flex-row-reverse sm:text-right' =>
                                                        $index === 0 || ($index !== 0 && $index % 2 === 0),
                                                ])>
                                                    <span @class([
                                                        'mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-lg',
                                                        'bg-primary/10 text-primary' => $index === 0,
                                                        'bg-slate-100 text-slate-500' => $index !== 0,
                                                    ]) aria-hidden="true">
                                                        <svg class="size-5" fill="none" stroke="currentColor"
                                                            stroke-width="1.7" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="{{ $step['icon'] }}" />
                                                        </svg>
                                                    </span>
                                                    <div class="min-w-0 flex-1">
                                                        <p
                                                            class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                            Tahap {{ $index + 1 }}
                                                        </p>

                                                        <h3 class="mt-1 font-heading text-sm font-bold text-secondary">
                                                            {{ $step['title'] }}
                                                        </h3>

                                                        @if ($index === 0)
                                                            <div class="mt-3 rounded-lg border border-slate-200 bg-white p-3 text-left">
                                                                @if ($selectedAppointment->ppat_meeting_date)
                                                                    <div class="space-y-2">
                                                                        <p class="text-xs leading-5 text-slate-600">
                                                                            Jadwal pertemuan PPAT:
                                                                            <span class="font-bold text-secondary">
                                                                                {{ $selectedAppointment->ppat_meeting_date->locale('id')->translatedFormat('l, d F Y') }}
                                                                            </span>
                                                                        </p>
                                                                        @if ($selectedAppointment->ppatMeetingStatusLabel())
                                                                            <span @class([
                                                                                'inline-flex rounded-full border px-2.5 py-1 text-[11px] font-bold',
                                                                                'border-amber-200 bg-amber-50 text-amber-800' => $selectedAppointment->ppat_meeting_status === \App\Models\Appointment::PPAT_MEETING_STATUS_REQUESTED,
                                                                                'border-emerald-200 bg-emerald-50 text-emerald-700' => $selectedAppointment->ppat_meeting_status === \App\Models\Appointment::PPAT_MEETING_STATUS_CONFIRMED,
                                                                                'border-violet-200 bg-violet-50 text-violet-700' => $selectedAppointment->ppat_meeting_status === \App\Models\Appointment::PPAT_MEETING_STATUS_RESCHEDULED,
                                                                            ])>
                                                                                {{ $selectedAppointment->ppatMeetingStatusLabel() }}
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                @else
                                                                    <p class="text-xs leading-5 text-slate-500">
                                                                        Menunggu penjual atau PPAT mengajukan tanggal pertemuan.
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        @elseif ($index === 1)
                                                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                                                Pembayaran uang muka aman melalui rekening penampung (escrow).
                                                            </p>
                                                        @elseif ($index === 2)
                                                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                                                Verifikasi keabsahan sertifikat di BPN dan validasi kewajiban pajak (PPh & BPHTB).
                                                            </p>
                                                        @elseif ($index === 3)
                                                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                                                Penyetoran sisa pelunasan harga properti ke rekening bersama (escrow) sebelum penandatanganan AJB.
                                                            </p>
                                                        @elseif ($index === 4)
                                                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                                                Penandatanganan resmi Akta Jual Beli bersama PPAT.
                                                            </p>
                                                        @elseif ($index === 5)
                                                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                                                Pencairan dana escrow kepada penjual setelah dokumen sah ditandatangani.
                                                            </p>
                                                        @elseif ($index === 6)
                                                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                                                Penerbitan resi dan penyerahan sertifikat atas nama pembeli baru.
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                class="col-start-1 row-start-1 flex size-10 items-center justify-center rounded-full border-2 border-white bg-slate-100 text-xs font-bold text-slate-600 shadow sm:col-start-2">
                                                {{ $index + 1 }}
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    @else
                        @if ($selectedAppointment)
                            <div class="space-y-4">
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <x-appointment-status-badge :status="$selectedAppointment->status" />
                                            <span class="text-xs font-semibold text-slate-600">
                                                {{ $selectedAppointment->scheduled_at->locale('id')->translatedFormat('l, d F Y') }}
                                                &middot;
                                                {{ $selectedAppointment->scheduled_at->format('H:i') }} WIB
                                            </span>
                                        </div>
                                    </div>

                                    @if ($selectedAppointment->status === \App\Models\Appointment::STATUS_REQUESTED)
                                        <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50/70 p-3 text-xs text-amber-800">
                                            Permintaan survei Anda telah dikirim dan sedang menunggu persetujuan dari pihak penjual.
                                        </div>
                                    @elseif ($selectedAppointment->hasPendingReschedule())
                                        <div class="mt-3 flex flex-col gap-2 rounded-lg border border-amber-200 bg-amber-50/70 p-3 sm:flex-row sm:items-center sm:justify-between">
                                            <p class="text-xs text-amber-800">
                                                Penjual menyarankan jadwal pengganti:
                                                <span class="font-bold">
                                                    {{ $selectedAppointment->reschedule_suggested_at->locale('id')->translatedFormat('l, d F Y') }}
                                                    - {{ $selectedAppointment->reschedule_suggested_at->format('H:i') }} WIB
                                                </span>
                                            </p>
                                            <a href="{{ route('buyer.survey-schedule') }}"
                                                class="inline-flex shrink-0 items-center justify-center rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-amber-700">
                                                Tanggapi jadwal
                                            </a>
                                        </div>
                                    @elseif ($selectedAppointment->status === \App\Models\Appointment::STATUS_CONFIRMED)
                                        <div class="mt-3 rounded-lg border border-sky-200 bg-sky-50/70 p-3 text-xs text-sky-800">
                                            Jadwal survei Anda telah dikonfirmasi oleh penjual. Silakan hadir di lokasi properti sesuai waktu yang ditentukan.
                                        </div>
                                    @elseif ($selectedAppointment->status === \App\Models\Appointment::STATUS_COMPLETED)
                                        <div class="mt-3 space-y-1.5 rounded-lg border border-emerald-200 bg-emerald-50/70 p-3 text-xs text-emerald-800">
                                            <p class="font-bold">Survei telah selesai dilaksanakan.</p>
                                            @if ($selectedAppointment->buyer_intent)
                                                <p>Keputusan pembeli: <span class="font-bold">{{ $selectedAppointment->buyerIntentLabel() }}</span></p>
                                            @endif
                                            @if ($selectedAppointment->result_notes)
                                                <p>Catatan: {{ $selectedAppointment->result_notes }}</p>
                                            @endif
                                        </div>
                                    @endif

                                    @if (filled($selectedAppointment->notes))
                                        <div class="mt-3 border-t border-slate-200 pt-3 text-xs text-slate-500">
                                            <span class="font-semibold text-slate-600">Catatan pengajuan Anda:</span>
                                            {{ $selectedAppointment->notes }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-slate-500">Belum ada survei yang diajukan untuk properti ini.</p>
                        @endif
                    @endif
                </x-card-dashboard>

                <x-card-dashboard title="Riwayat survei saya" subtitle="Seluruh riwayat kunjungan yang Anda jadwalkan untuk properti ini">
                    @if ($appointments->isEmpty())
                        <p class="text-sm text-slate-500">Belum ada riwayat survei.</p>
                    @else
                        <ul class="divide-y divide-slate-100">
                            @foreach ($appointments as $appointment)
                                <li class="py-4 first:pt-0 last:pb-0">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <x-appointment-status-badge :status="$appointment->status" />
                                                <span class="text-xs font-semibold text-slate-500">
                                                    {{ $appointment->scheduled_at->locale('id')->translatedFormat('d M Y') }}
                                                    &middot;
                                                    {{ $appointment->scheduled_at->format('H:i') }} WIB
                                                </span>
                                            </div>

                                            @if (filled($appointment->result_notes))
                                                <p class="mt-2 text-xs leading-5 text-slate-600">
                                                    <span class="font-bold text-slate-500">Hasil survei:</span>
                                                    {{ $appointment->result_notes }}
                                                </p>
                                            @endif

                                            @if (filled($appointment->buyer_intent))
                                                <p class="mt-1 text-xs leading-5 text-slate-600">
                                                    <span class="font-bold text-slate-500">Keputusan:</span>
                                                    {{ $appointment->buyerIntentLabel() }}
                                                </p>
                                            @endif

                                            @if (filled($appointment->notes))
                                                <p class="mt-1 text-xs leading-5 text-slate-400">
                                                    <span class="font-semibold">Catatan Anda:</span>
                                                    {{ $appointment->notes }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="shrink-0 text-xs text-slate-400 sm:pt-0.5">
                                            {{ $appointment->created_at->locale('id')->diffForHumans() }}
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card-dashboard>
            </div>

            <aside class="space-y-6">
                <x-card-dashboard title="Progres properti" subtitle="Berdasarkan status survei terbaru">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <span @class([
                                'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold',
                                $progressTone['badge'],
                            ])>
                                {{ $progress['label'] }}
                            </span>
                            <p class="mt-3 text-sm leading-6 text-slate-500">
                                {{ $progress['description'] }}
                            </p>
                        </div>
                        <span class="font-heading text-2xl font-bold text-secondary">
                            {{ $progress['percentage'] }}%
                        </span>
                    </div>

                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full transition-all duration-500 {{ $progressTone['bar'] }}"
                            style="width: {{ $progress['percentage'] }}%"></div>
                    </div>

                    <ol class="mt-5 space-y-4">
                        @foreach ($progressSteps as $index => $step)
                            @php
                                $isComplete = $index < $progress['completed_steps'];
                                $isCurrent =
                                    !$isComplete && !$progress['stopped'] && $index === $progress['completed_steps'];
                            @endphp

                            <li class="flex items-start gap-3">
                                <span @class([
                                    'flex size-7 shrink-0 items-center justify-center rounded-full border text-[11px] font-bold',
                                    'border-emerald-200 bg-emerald-50 text-emerald-600' => $isComplete,
                                    'border-primary/30 bg-primary/10 text-primary' => $isCurrent,
                                    'border-slate-200 bg-white text-slate-400' => !$isComplete && !$isCurrent,
                                ])>
                                    @if ($isComplete)
                                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                            viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                                        </svg>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </span>

                                <div class="min-w-0 pt-0.5">
                                    <p @class([
                                        'text-sm font-semibold',
                                        'text-secondary' => $isComplete || $isCurrent,
                                        'text-slate-400' => !$isComplete && !$isCurrent,
                                    ])>
                                        {{ $step }}
                                    </p>
                                    @if ($isCurrent)
                                        <p class="mt-0.5 text-[11px] text-slate-400">Sedang berlangsung</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    @if ($latestAppointment)
                        <div class="mt-5 border-t border-slate-100 pt-4">
                            <div class="flex items-center justify-between gap-3">
                                <x-appointment-status-badge :status="$latestAppointment->status" />
                                <span class="text-right text-[11px] font-medium text-slate-400">
                                    {{ $latestAppointment->scheduled_at->locale('id')->translatedFormat('d M Y') }}
                                    &middot;
                                    {{ $latestAppointment->scheduled_at->format('H:i') }} WIB
                                </span>
                            </div>
                        </div>
                    @endif
                </x-card-dashboard>

                <x-card-dashboard title="Informasi Penjual">
                    @if ($property->seller)
                        <div class="flex items-center gap-3">
                            <img src="{{ $property->seller->avatar_url }}" alt="Foto profil {{ $property->seller->name }}"
                                class="size-11 rounded-full object-cover">
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-heading text-sm font-bold text-secondary">
                                    {{ $property->seller->name }}
                                </p>
                                <p class="truncate text-xs text-slate-400">
                                    {{ $property->seller->email }}
                                </p>
                            </div>
                        </div>
                        @if (filled($property->seller->phone))
                            <div class="mt-4 border-t border-slate-100 pt-3">
                                <p class="text-xs font-semibold text-slate-400">Nomor Telepon / WhatsApp</p>
                                <p class="mt-1 text-sm font-semibold text-secondary">
                                    +{{ $property->seller->phone }}
                                </p>
                            </div>
                        @endif
                    @else
                        <p class="text-sm text-slate-400">Informasi penjual tidak tersedia.</p>
                    @endif
                </x-card-dashboard>

                <x-card-dashboard title="PPAT yang ditugaskan">
                    @if ($property->assignedPpat)
                        <div class="flex items-center gap-3">
                            <img src="{{ $property->assignedPpat->avatar_url }}" alt="Foto profil {{ $property->assignedPpat->name }}"
                                class="size-11 rounded-full object-cover">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <p class="truncate font-heading text-sm font-bold text-secondary">
                                        {{ $property->assignedPpat->name }}
                                    </p>
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                        Terverifikasi
                                    </span>
                                </div>
                                <p class="truncate text-xs text-slate-400">
                                    {{ $property->assignedPpat->email }}
                                </p>
                            </div>
                        </div>
                        @if (filled($property->assignedPpat->phone))
                            <div class="mt-4 border-t border-slate-100 pt-3">
                                <p class="text-xs font-semibold text-slate-400">Kontak PPAT</p>
                                <p class="mt-1 text-sm font-semibold text-secondary">
                                    +{{ $property->assignedPpat->phone }}
                                </p>
                            </div>
                        @endif
                    @else
                        <p class="text-sm text-slate-400">PPAT belum ditugaskan untuk properti ini.</p>
                    @endif
                </x-card-dashboard>

                <x-card-dashboard title="Spesifikasi properti">
                    <dl class="space-y-3">
                        @foreach ($propertyFacts as $label => $value)
                            <div
                                class="flex items-start justify-between gap-4 border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                                <dt class="text-xs font-semibold text-slate-400">{{ $label }}</dt>
                                <dd class="text-right text-sm font-semibold text-secondary">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-card-dashboard>

                <x-card-dashboard title="Lokasi properti">
                    <p class="text-sm font-semibold leading-6 text-secondary">
                        {{ $property->address }}
                    </p>
                    <p class="mt-1 text-sm leading-6 text-slate-500">
                        {{ collect([$property->district, $property->city, $property->province, $property->postal_code])->filter()->implode(', ') }}
                    </p>
                    <a href="{{ $mapUrl }}" target="_blank" rel="noopener"
                        class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-primary transition hover:text-primary/80">
                        Buka di peta
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5h5v5m0-5-9 9M19 14v5H5V5h5" />
                        </svg>
                    </a>
                </x-card-dashboard>
            </aside>
        </div>
    </div>
@endsection

