@extends('layouts.app')
@section('page-title', 'Detail Properti')
@section('content')
    @php
        $propertyFacts = array_filter([
            'Tipe properti' => $property->property_type === 'tanah' ? 'Tanah' : 'Bangunan',
            'Jenis' => filled($property->type) ? ucfirst((string) $property->type) : null,
            'Kategori' => $property->category === 'rent' ? 'Disewakan' : 'Dijual',
            'Sertifikat' => $property->certificate_type,
            'Luas tanah' => filled($property->land_area_sqm)
                ? number_format((float) $property->land_area_sqm, 0, ',', '.').' m²'
                : null,
            'Luas bangunan' => filled($property->building_area_sqm)
                ? number_format((float) $property->building_area_sqm, 0, ',', '.').' m²'
                : null,
            'Kamar tidur' => filled($property->rooms) ? $property->rooms.' KT' : null,
            'Kamar mandi' => filled($property->bathrooms) ? $property->bathrooms.' KM' : null,
            'Lantai' => $property->floors,
            'Kondisi' => $property->condition,
            'Lebar depan' => filled($property->front_width)
                ? number_format((float) $property->front_width, 0, ',', '.').' m'
                : null,
            'Kontur tanah' => $property->land_contour,
            'Akses jalan' => $property->road_access,
            'Zona' => $property->zone_type,
            'PPAT' => $property->assignedPpat?->name,
        ], fn ($value): bool => filled($value));

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
        $rentPeriod = $property->category === 'rent'
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
                <a href="{{ route('home') }}" class="rounded transition hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                    Beranda
                </a>
                <span aria-hidden="true" class="text-slate-300">&gt;</span>
                <a href="{{ route('seller.my-properties') }}"
                    class="rounded transition hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                    Properti Saya
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
                    <a href="{{ route('seller.my-properties') }}"
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
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14 5h5v5m0-5-9 9M19 14v5H5V5h5" />
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

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <p class="font-bold">Hasil survei belum dapat disimpan.</p>
                <ul class="mt-1.5 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
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
                                {{ $property->negotiable ? 'Harga dapat dinegosiasi dengan calon pembeli.' : 'Harga sesuai yang tertera pada listing.' }}
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
                                        {{ filled($property->building_area_sqm) ? number_format((float) $property->building_area_sqm, 0, ',', '.').' m²' : '-' }}
                                    </dd>
                                </div>
                                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                                    <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Kamar tidur
                                    </dt>
                                    <dd class="mt-1 text-sm font-bold text-secondary">
                                        {{ filled($property->rooms) ? $property->rooms.' KT' : '-' }}
                                    </dd>
                                </div>
                                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                                    <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Kamar mandi
                                    </dt>
                                    <dd class="mt-1 text-sm font-bold text-secondary">
                                        {{ filled($property->bathrooms) ? $property->bathrooms.' KM' : '-' }}
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
                    subtitle="Informasi yang tampil pada halaman properti publik">
                    <p class="whitespace-pre-line text-sm leading-7 text-slate-600">
                        {{ filled($property->description) ? $property->description : 'Belum ada deskripsi properti.' }}
                    </p>
                </x-card-dashboard>

                <x-card-dashboard id="survey" title="Survei properti"
                    subtitle="Hasil dan progres survei terbaru untuk properti ini">
                    <x-slot name="actions">
                        <a href="{{ route('seller.request-survey') }}"
                            class="text-sm font-semibold text-primary hover:underline">
                            Semua permintaan
                        </a>
                    </x-slot>

                    @if ($selectedAppointment === null)
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/70 px-5 py-10 text-center">
                            <p class="font-heading font-bold text-slate-600">Belum ada pengajuan survei</p>
                            <p class="mt-1 text-sm text-slate-400">
                                Permintaan survei dari calon pembeli akan muncul di sini.
                            </p>
                        </div>
                    @else
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex min-w-0 items-start gap-3">
                                    <img src="{{ $selectedAppointment->buyer->avatar_url }}"
                                        alt="Avatar {{ $selectedAppointment->buyer->name }}"
                                        class="size-11 shrink-0 rounded-full object-cover ring-2 ring-white">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-heading font-bold text-secondary">
                                                {{ $selectedAppointment->buyer->name }}
                                            </p>
                                            @if ($latestAppointment && ! $selectedAppointment->is($latestAppointment))
                                                <span
                                                    class="rounded-full bg-slate-200/70 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-500">
                                                    Survei sebelumnya
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-0.5 truncate text-xs text-slate-500">
                                            {{ $selectedAppointment->buyer->email }}
                                        </p>
                                        @if (filled($selectedAppointment->buyer->phone))
                                            <a href="https://wa.me/{{ $selectedAppointment->buyer->phone }}" target="_blank"
                                                rel="noopener"
                                                class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline">
                                                +{{ $selectedAppointment->buyer->phone }}
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <x-appointment-status-badge :status="$selectedAppointment->status" class="w-fit shrink-0" />
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="rounded-lg border border-slate-100 bg-white p-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Jadwal survei
                                    </p>
                                    <p class="mt-1 text-sm font-bold text-secondary">
                                        {{ $selectedAppointment->scheduled_at->locale('id')->translatedFormat('l, d F Y') }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ $selectedAppointment->scheduled_at->format('H:i') }} WIB
                                    </p>
                                </div>
                                <div class="rounded-lg border border-slate-100 bg-white p-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Diajukan pada
                                    </p>
                                    <p class="mt-1 text-sm font-bold text-secondary">
                                        {{ $selectedAppointment->created_at->locale('id')->translatedFormat('d F Y') }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ $selectedAppointment->created_at->format('H:i') }} WIB
                                    </p>
                                </div>
                            </div>

                            @if (filled($selectedAppointment->notes))
                                <div class="mt-3 rounded-lg border border-slate-100 bg-white p-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Catatan pembeli
                                    </p>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">
                                        {{ $selectedAppointment->notes }}
                                    </p>
                                </div>
                            @endif

                            @if (filled($selectedAppointment->result_notes))
                                <div class="mt-3 rounded-lg border border-emerald-100 bg-emerald-50 p-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-600">
                                        Hasil survei
                                    </p>
                                    <p class="mt-1 text-sm leading-6 text-emerald-800">
                                        {{ $selectedAppointment->result_notes }}
                                    </p>
                                    @if ($selectedAppointment->completed_at)
                                        <p class="mt-1.5 text-[11px] font-medium text-emerald-600">
                                            Dicatat
                                            {{ $selectedAppointment->completed_at->locale('id')->translatedFormat('d F Y, H:i') }}
                                            WIB
                                        </p>
                                    @endif
                                </div>
                            @endif

                            @if ($selectedAppointment->status === \App\Models\Appointment::STATUS_CONFIRMED)
                                <div class="mt-5 border-t border-slate-200 pt-5">
                                    <h3 class="font-heading text-sm font-bold text-secondary">Catat hasil survei</h3>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        Hasil survei hanya memperbarui progres survei. Status tayang properti tidak
                                        berubah.
                                    </p>

                                    <form method="POST"
                                        action="{{ route('seller.my-properties.surveys.update', ['property' => $property->uuid, 'appointment' => $selectedAppointment->id]) }}"
                                        class="mt-4 space-y-4">
                                        @csrf
                                        @method('PATCH')

                                        <x-form-select-dashboard label="Hasil survei" name="status" required>
                                            <option value="completed" @selected(old('status', 'completed') === 'completed')>
                                                Survei selesai
                                            </option>
                                            <option value="cancelled" @selected(old('status') === 'cancelled')>
                                                Survei dibatalkan
                                            </option>
                                        </x-form-select-dashboard>

                                        <x-form-text-area-dashboard label="Catatan hasil survei" name="result_notes"
                                            rows="3"
                                            placeholder="Contoh: Calon pembeli tertarik dan akan melanjutkan diskusi harga.">
                                            {{ old('result_notes', $selectedAppointment->result_notes) }}
                                        </x-form-text-area-dashboard>

                                        <div class="flex justify-end">
                                            <x-button type="submit" variant="primary" size="sm">
                                                Simpan hasil survei
                                            </x-button>
                                        </div>
                                    </form>
                                </div>
                            @elseif ($selectedAppointment->isAwaitingReview())
                                <div
                                    class="mt-4 flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-sm text-amber-800">
                                        Permintaan survei ini masih menunggu persetujuan Anda.
                                    </p>
                                    <a href="{{ route('seller.request-survey') }}"
                                        class="inline-flex shrink-0 items-center justify-center rounded-lg border border-amber-300 bg-white px-3.5 py-2 text-xs font-bold text-amber-700 transition hover:bg-amber-100">
                                        Tinjau permintaan
                                    </a>
                                </div>
                            @elseif ($selectedAppointment->hasPendingReschedule())
                                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                                    Jadwal pengganti sudah dikirim ke pembeli:
                                    <span class="font-bold">
                                        {{ $selectedAppointment->reschedule_suggested_at->locale('id')->translatedFormat('l, d F Y') }}
                                        &middot;
                                        {{ $selectedAppointment->reschedule_suggested_at->format('H:i') }} WIB
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif
                </x-card-dashboard>

                <x-card-dashboard title="Riwayat survei" subtitle="Seluruh pengajuan survei untuk properti ini">
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
                                            <p class="mt-2 text-sm font-semibold text-secondary">
                                                {{ $appointment->buyer->name }}
                                            </p>
                                            <p class="text-xs text-slate-400">
                                                {{ $appointment->buyer->email }}
                                            </p>

                                            @if (filled($appointment->result_notes))
                                                <p class="mt-2 text-xs leading-5 text-slate-600">
                                                    <span class="font-bold text-slate-500">Hasil:</span>
                                                    {{ $appointment->result_notes }}
                                                </p>
                                            @endif

                                            @if (filled($appointment->notes))
                                                <p class="mt-1 text-xs leading-5 text-slate-400">
                                                    <span class="font-semibold">Catatan pembeli:</span>
                                                    {{ $appointment->notes }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="shrink-0 sm:pt-0.5">
                                            @if ($appointment->status === \App\Models\Appointment::STATUS_CONFIRMED)
                                                <a href="{{ route('seller.my-properties.show', ['property' => $property->uuid, 'appointment' => $appointment->id]).'#survey' }}"
                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50">
                                                    Catat hasil
                                                    <span aria-hidden="true">&rarr;</span>
                                                </a>
                                            @else
                                                <span class="text-xs text-slate-400">
                                                    {{ $appointment->statusLabel() }}
                                                </span>
                                            @endif
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
                                $isCurrent = ! $isComplete && ! $progress['stopped'] && $index === $progress['completed_steps'];
                            @endphp

                            <li class="flex items-start gap-3">
                                <span @class([
                                    'flex size-7 shrink-0 items-center justify-center rounded-full border text-[11px] font-bold',
                                    'border-emerald-200 bg-emerald-50 text-emerald-600' => $isComplete,
                                    'border-primary/30 bg-primary/10 text-primary' => $isCurrent,
                                    'border-slate-200 bg-white text-slate-400' => ! $isComplete && ! $isCurrent,
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
                                        'text-slate-400' => ! $isComplete && ! $isCurrent,
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

                <x-card-dashboard title="Spesifikasi properti">
                    <dl class="space-y-3">
                        @foreach ($propertyFacts as $label => $value)
                            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-3 last:border-0 last:pb-0">
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
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14 5h5v5m0-5-9 9M19 14v5H5V5h5" />
                        </svg>
                    </a>
                </x-card-dashboard>
            </aside>
        </div>
    </div>
@endsection
