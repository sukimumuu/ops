@extends('layouts.app')
@section('page-title', 'Jadwal Survei')

@section('content')
    @php
        $bookingStatusStyles = [
            'requested' => [
                'label' => 'Menunggu persetujuan',
                'dot' => 'bg-amber-500',
                'ring' => 'ring-amber-300 hover:ring-amber-500',
                'badge' => 'border-amber-200 bg-amber-50 text-amber-800',
            ],
            'confirmed' => [
                'label' => 'Disetujui',
                'dot' => 'bg-emerald-500',
                'ring' => 'ring-emerald-300 hover:ring-emerald-500',
                'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            ],
            'rejected' => [
                'label' => 'Ditolak',
                'dot' => 'bg-rose-500',
                'ring' => 'ring-rose-300 hover:ring-rose-500',
                'badge' => 'border-rose-200 bg-rose-50 text-rose-700',
            ],
            'completed' => [
                'label' => 'Selesai',
                'dot' => 'bg-sky-500',
                'ring' => 'ring-sky-300 hover:ring-sky-500',
                'badge' => 'border-sky-200 bg-sky-50 text-sky-700',
            ],
            'cancelled' => [
                'label' => 'Dibatalkan',
                'dot' => 'bg-slate-400',
                'ring' => 'ring-slate-300 hover:ring-slate-500',
                'badge' => 'border-slate-200 bg-slate-50 text-slate-600',
            ],
        ];
        $pendingSurveyCount = $appointments->where('status', 'requested')->count();
    @endphp

    <div class="space-y-5" x-data="{ bookings: @js($calendarBookings), statusStyles: @js($bookingStatusStyles), selectedAppointment: null, openBooking(id) { this.selectedAppointment = this.bookings.find((booking) => booking.id === id) }, statusStyle(statusKey) { return this.statusStyles[statusKey] ?? this.statusStyles.cancelled } }" x-on:keydown.escape.window="selectedAppointment = null">
        <header class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Operasional kantor</p>
                <h1 class="mt-1 text-2xl font-bold text-secondary">Jadwal survei</h1>
                <p class="mt-1 text-sm text-slate-500">Jadwal kunjungan untuk properti yang ditugaskan kepada Anda.</p>
            </div>
            <p class="text-xs text-slate-500">
                {{ $appointments->count() }} survei bulan ini
                @if ($pendingSurveyCount > 0)
                    <span class="font-semibold text-amber-600">· {{ $pendingSurveyCount }} menunggu persetujuan</span>
                @endif
            </p>
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

        <section aria-label="Kalender jadwal survei"
            class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xs">
            <div
                class="flex flex-col gap-4 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div>
                    <h2 class="text-lg font-semibold text-secondary">{{ $month->locale('id')->translatedFormat('F Y') }}
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500">Pilih avatar untuk membuka rincian survei.</p>
                </div>
                <nav aria-label="Navigasi bulan" class="flex items-center gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['month' => $month->copy()->subMonth()->format('Y-m')]) }}"
                        aria-label="Bulan sebelumnya"
                        class="inline-flex size-9 items-center justify-center rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                        </svg>
                    </a>
                    <a href="{{ route('land-deed.index') }}"
                        class="rounded-md border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">Hari
                        ini</a>
                    <a href="{{ request()->fullUrlWithQuery(['month' => $month->copy()->addMonth()->format('Y-m')]) }}"
                        aria-label="Bulan berikutnya"
                        class="inline-flex size-9 items-center justify-center rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </a>
                </nav>
            </div>

            <div class="overflow-x-auto">
                <div class="min-w-[720px]">
                    <div
                        class="grid grid-cols-7 border-b border-slate-200 bg-slate-50 text-center text-[11px] font-semibold uppercase text-slate-500">
                        @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $weekday)
                            <div class="py-3">{{ $weekday }}</div>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-7">
                        @foreach ($calendarDays as $day)
                            @php($dayAppointments = $appointmentsByDate->get($day->toDateString(), collect()))
                            <div
                                class="min-h-28 border-b border-r border-slate-100 p-2.5 {{ $day->month === $month->month ? 'bg-white' : 'bg-slate-50/70' }}">
                                <div class="flex items-start justify-between">
                                    <span
                                        class="inline-flex size-7 items-center justify-center rounded-full text-xs {{ $day->isToday() ? 'bg-primary font-bold text-white' : ($day->month === $month->month ? 'font-medium text-slate-700' : 'text-slate-400') }}">{{ $day->day }}</span>
                                    @if ($dayAppointments->isNotEmpty())
                                        <span
                                            class="text-[10px] font-medium text-slate-400">{{ $dayAppointments->count() }}
                                            jadwal</span>
                                    @endif
                                </div>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach ($dayAppointments as $appointment)
                                        @php($statusStyle = $bookingStatusStyles[$appointment->status] ?? $bookingStatusStyles['cancelled'])
                                        <button type="button" x-on:click="openBooking({{ $appointment->id }})"
                                            title="{{ $appointment->buyer->name }} · {{ $appointment->scheduled_at->format('H:i') }} · {{ $appointment->property->title }} · {{ $statusStyle['label'] }}"
                                            aria-label="Buka survei {{ $appointment->buyer->name }}, {{ $appointment->scheduled_at->format('H:i') }}, {{ $appointment->property->title }}, status {{ $statusStyle['label'] }}"
                                            class="relative size-8 rounded-full border-2 border-white ring-1 transition hover:scale-110 focus:outline-none focus:ring-2 focus:ring-primary {{ $statusStyle['ring'] }}">
                                            <img src="{{ $appointment->buyer->avatar_url }}"
                                                alt="{{ $appointment->buyer->name }}"
                                                class="size-full rounded-full object-cover">
                                            <span aria-hidden="true"
                                                class="pointer-events-none absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border-2 border-white shadow-sm {{ $statusStyle['dot'] }}"></span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <footer class="flex flex-wrap items-center gap-x-5 gap-y-2 px-4 py-3 text-xs text-slate-500 sm:px-5">
                @foreach ($bookingStatusStyles as $statusStyle)
                    <span class="inline-flex items-center gap-2"><span
                            class="size-2 rounded-full {{ $statusStyle['dot'] }}"></span>{{ $statusStyle['label'] }}</span>
                @endforeach
                <span class="text-slate-400">{{ $appointments->where('status', 'confirmed')->count() }} terjadwal ·
                    {{ $pendingSurveyCount }} menunggu persetujuan</span>
            </footer>
        </section>

        <div x-cloak x-show="selectedAppointment" class="fixed inset-0 z-50" role="presentation">
            <button type="button" x-on:click="selectedAppointment = null" aria-label="Tutup rincian survei"
                class="absolute inset-0 size-full cursor-default bg-slate-950/40"></button>
            <aside x-show="selectedAppointment" x-transition:enter="transform transition ease-out duration-200"
                x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-150" x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full" role="dialog" aria-modal="true"
                aria-labelledby="booking-drawer-title"
                class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-white shadow-2xl">
                <template x-if="selectedAppointment">
                    <div class="flex h-full flex-col">
                        <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
                            <div>
                                <p class="text-xs font-semibold uppercase text-primary">Rincian booking</p>
                                <h2 id="booking-drawer-title" class="mt-1 text-lg font-bold text-secondary"
                                    x-text="selectedAppointment.client_name"></h2>
                            </div>
                            <button type="button" x-on:click="selectedAppointment = null" aria-label="Tutup"
                                class="inline-flex size-9 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-slate-800">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex-1 space-y-6 overflow-y-auto px-5 py-5">
                            <div class="flex items-center gap-3">
                                <img x-bind:src="selectedAppointment.avatar_url"
                                    x-bind:alt="selectedAppointment.client_name"
                                    class="size-14 rounded-full object-cover ring-1 ring-slate-200">
                                <div>
                                    <p class="font-semibold text-slate-800" x-text="selectedAppointment.client_name"></p>
                                    <p class="mt-1 text-xs text-slate-500">Klien survei</p>
                                </div>
                            </div>
                            <dl class="divide-y divide-slate-100 rounded-md border border-slate-200">
                                <div class="px-4 py-3">
                                    <dt class="text-xs text-slate-500">Tanggal dan waktu</dt>
                                    <dd class="mt-1 text-sm font-medium text-slate-800"><span
                                            x-text="selectedAppointment.date"></span>, pukul <span
                                            x-text="selectedAppointment.time"></span> WIB</dd>
                                </div>
                                <template x-if="selectedAppointment.reschedule_date">
                                    <div class="px-4 py-3">
                                        <dt class="text-xs text-slate-500">Saran jadwal pengganti</dt>
                                        <dd class="mt-1 text-sm font-semibold text-primary"><span
                                                x-text="selectedAppointment.reschedule_date"></span>, pukul <span
                                                x-text="selectedAppointment.reschedule_time"></span> WIB</dd>
                                    </div>
                                </template>
                                <div class="px-4 py-3">
                                    <dt class="text-xs text-slate-500">Status</dt>
                                    <dd class="mt-1">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold"
                                            x-bind:class="statusStyle(selectedAppointment.status_key).badge">
                                            <span class="size-1.5 rounded-full"
                                                x-bind:class="statusStyle(selectedAppointment.status_key).dot"></span>
                                            <span x-text="selectedAppointment.status"></span>
                                        </span>
                                    </dd>
                                </div>
                                <div class="px-4 py-3">
                                    <dt class="text-xs text-slate-500">Properti</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-800"><span
                                            x-text="selectedAppointment.property_certificate"></span> · <span
                                            x-text="selectedAppointment.property_title"></span></dd>
                                    <dd class="mt-1 text-xs leading-relaxed text-slate-500"><span
                                            x-text="selectedAppointment.property_address"></span>, <span
                                            x-text="selectedAppointment.property_location"></span></dd>
                                </div>
                                <div class="px-4 py-3">
                                    <dt class="text-xs text-slate-500">Penjual</dt>
                                    <dd class="mt-1 text-sm font-medium text-slate-800"
                                        x-text="selectedAppointment.seller_name"></dd>
                                </div>
                                <template x-if="selectedAppointment.notes">
                                    <div class="px-4 py-3">
                                        <dt class="text-xs text-slate-500">Catatan</dt>
                                        <dd class="mt-1 whitespace-pre-line text-sm leading-relaxed text-slate-700"
                                            x-text="selectedAppointment.notes"></dd>
                                    </div>
                                </template>
                            </dl>
                        </div>
                        <div class="border-t border-slate-200 px-5 py-4">
                            <template
                                x-if="selectedAppointment.can_review && selectedAppointment.status_key === 'requested'">
                                <div class="space-y-3" x-data="{ rejecting: false }">
                                    <p class="text-xs leading-relaxed text-slate-500">Setujui untuk mengonfirmasi jadwal
                                        survei, atau tolak dan tawarkan jadwal pengganti kepada pembeli.</p>

                                    <div class="flex flex-col gap-2 sm:flex-row" x-show="! rejecting">
                                        <form method="POST" x-bind:action="selectedAppointment.status_url"
                                            class="flex-1">
                                            @csrf
                                            <button type="submit" name="status" value="confirmed"
                                                class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                                <svg class="size-4" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m5 13 4 4L19 7" />
                                                </svg>
                                                Setujui survei
                                            </button>
                                        </form>
                                        <button type="button" x-on:click="rejecting = true"
                                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-md border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m6 6 12 12M18 6 6 18" />
                                            </svg>
                                            Tolak &amp; atur jadwal
                                        </button>
                                    </div>

                                    <form x-show="rejecting" method="POST" x-bind:action="selectedAppointment.status_url"
                                        class="space-y-3 rounded-md border border-rose-200 bg-rose-50/50 p-3">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">

                                        <div>
                                            <label for="suggested-date"
                                                class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Tanggal
                                                pengganti</label>
                                            <input type="date" id="suggested-date" name="suggested_date"
                                                min="{{ now()->format('Y-m-d') }}" required
                                                class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                        </div>

                                        <div>
                                            <span
                                                class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Waktu
                                                pengganti</span>
                                            <div class="mt-1 grid grid-cols-3 gap-2" role="radiogroup"
                                                aria-label="Pilih waktu jadwal pengganti">
                                                @foreach ($timeSlots as $slot)
                                                    <label class="cursor-pointer">
                                                        <input type="radio" name="suggested_time"
                                                            value="{{ $slot }}" class="peer sr-only" required>
                                                        <span
                                                            class="flex flex-col items-center gap-0.5 rounded-md border border-slate-300 bg-white px-2 py-2 text-center text-sm font-extrabold text-secondary transition hover:border-primary/50 peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-primary/30">
                                                            {{ str_replace(':', '.', $slot) }}
                                                            <span
                                                                class="text-[10px] font-semibold uppercase tracking-wider opacity-60">WIB</span>
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        <p class="text-xs leading-relaxed text-slate-500">Jadwal pengganti akan dikirim ke
                                            pembeli untuk dikonfirmasi.</p>

                                        <div class="flex flex-col gap-2 sm:flex-row">
                                            <button type="submit"
                                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-md bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                                                Kirim jadwal pengganti
                                            </button>
                                            <button type="button" x-on:click="rejecting = false"
                                                class="inline-flex flex-1 items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                                Batal
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </template>
                            <p x-show="! selectedAppointment.can_review || selectedAppointment.status_key !== 'requested'"
                                class="mb-3 text-xs leading-relaxed text-slate-500">
                                <span x-show="selectedAppointment.can_review">Pengajuan survei ini sudah diproses.</span>
                                <span x-show="! selectedAppointment.can_review">Anda tidak memiliki akses untuk meninjau
                                    pengajuan ini.</span>
                            </p>
                            <button type="button" x-on:click="selectedAppointment = null"
                                class="w-full rounded-md bg-secondary px-4 py-2.5 text-sm font-semibold text-white hover:bg-secondary/90 mt-3">Tutup
                                rincian</button>
                        </div>
                    </div>
                </template>
            </aside>
        </div>
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
