@props([
    'month',
    'calendarDays',
    'appointmentsByDate',
    'bookings',
    'appointments',
    'perspective' => 'buyer',
    'resetRoute',
    'title' => 'Kalender jadwal survei',
    'description' => 'Pilih avatar untuk membuka rincian survei.',
])

@php
    $isSeller = $perspective === 'seller';
    $counterpartLabel = $isSeller ? 'Calon pembeli' : 'Penjual properti';
    $timeSlots = \App\Models\Appointment::TIME_SLOTS;
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

<div id="survey-calendar" class="space-y-5" x-data="{ bookings: @js($bookings), statusStyles: @js($bookingStatusStyles), selectedAppointment: null, openBooking(id) { this.selectedAppointment = this.bookings.find((booking) => booking.id === id) }, statusStyle(statusKey) { return this.statusStyles[statusKey] ?? this.statusStyles.cancelled } }" x-on:keydown.escape.window="selectedAppointment = null">
    <section aria-label="{{ $title }}"
        class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xs">
        <div
            class="flex flex-col gap-4 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div>
                <h2 class="text-lg font-semibold text-secondary">{{ $month->locale('id')->translatedFormat('F Y') }}
                </h2>
                <p class="mt-0.5 text-xs text-slate-500">{{ $description }}</p>
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
                <a href="{{ route($resetRoute) }}"
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
                                    @php($counterpart = $isSeller ? $appointment->buyer : $appointment->seller)
                                    <button type="button" x-on:click="openBooking({{ $appointment->id }})"
                                        title="{{ $counterpart->name }} · {{ $appointment->scheduled_at->format('H:i') }} · {{ $appointment->property->title }} · {{ $statusStyle['label'] }}"
                                        aria-label="Buka survei {{ $counterpart->name }}, {{ $appointment->scheduled_at->format('H:i') }}, {{ $appointment->property->title }}, status {{ $statusStyle['label'] }}"
                                        class="relative size-8 rounded-full border-2 border-white ring-1 transition hover:scale-110 focus:outline-none focus:ring-2 focus:ring-primary {{ $statusStyle['ring'] }}">
                                        <img src="{{ $counterpart->avatar_url }}" alt="{{ $counterpart->name }}"
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
            <span class="text-slate-400">{{ $appointments->where('status', 'confirmed')->count() }}
                {{ $isSeller ? 'terjadwal' : 'disetujui' }} ·
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
                            <p class="text-xs font-semibold uppercase text-primary">
                                {{ $isSeller ? 'Rincian permintaan survei' : 'Rincian jadwal' }}
                            </p>
                            <h2 id="booking-drawer-title" class="mt-1 text-lg font-bold text-secondary"
                                x-text="selectedAppointment.counterpart_name"></h2>
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
                                x-bind:alt="selectedAppointment.counterpart_name"
                                class="size-14 rounded-full object-cover ring-1 ring-slate-200">
                            <div>
                                <p class="font-semibold text-slate-800" x-text="selectedAppointment.counterpart_name">
                                </p>
                                <p class="mt-1 text-xs text-slate-500">{{ $counterpartLabel }}</p>
                            </div>
                        </div>
                        <div class="rounded-md border border-slate-200 bg-slate-50/60 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Kontak
                                {{ $counterpartLabel }}</p>
                            <div class="mt-3 space-y-2.5">
                                <template x-if="selectedAppointment.counterpart_phone">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span
                                            class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                                            <svg class="size-4 shrink-0 text-slate-400" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="1.8"
                                                aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.4 19.4 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7l.5 2.8a2 2 0 0 1-.6 1.9L7.1 10a16 16 0 0 0 6 6l1.6-1.9a2 2 0 0 1 1.9-.6l2.8.5a2 2 0 0 1 1.6 1.9Z" />
                                            </svg>
                                            <span x-text="`+${selectedAppointment.counterpart_phone}`"></span>
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <a x-bind:href="`https://wa.me/${selectedAppointment.counterpart_phone}`"
                                                target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">
                                                WhatsApp
                                            </a>
                                            <a x-bind:href="`tel:+${selectedAppointment.counterpart_phone}`"
                                                class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-100">
                                                Telepon
                                            </a>
                                        </span>
                                    </div>
                                </template>
                                <template x-if="selectedAppointment.counterpart_email">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span
                                            class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                                            <svg class="size-4 shrink-0 text-slate-400" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="1.8"
                                                aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Zm0 1 8 6 8-6" />
                                            </svg>
                                            <span class="break-all"
                                                x-text="selectedAppointment.counterpart_email"></span>
                                        </span>
                                        <a x-bind:href="`mailto:${selectedAppointment.counterpart_email}`"
                                            class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-100">
                                            Email
                                        </a>
                                    </div>
                                </template>
                                <template
                                    x-if="! selectedAppointment.counterpart_phone && ! selectedAppointment.counterpart_email">
                                    <p class="text-sm text-slate-500">Informasi kontak belum tersedia.</p>
                                </template>
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
                                <template x-if="selectedAppointment.property_maps_url">
                                    <dd class="mt-2">
                                        <a x-bind:href="selectedAppointment.property_maps_url" target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-primary transition hover:bg-slate-50">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 21s-7-5.7-7-11a7 7 0 1 1 14 0c0 5.3-7 11-7 11Z" />
                                                <circle cx="12" cy="10" r="2.5" />
                                            </svg>
                                            Lihat lokasi di peta
                                        </a>
                                    </dd>
                                </template>
                            </div>
                            <div class="px-4 py-3">
                                <dt class="text-xs text-slate-500">{{ $counterpartLabel }}</dt>
                                <dd class="mt-1 text-sm font-medium text-slate-800"
                                    x-text="selectedAppointment.counterpart_name"></dd>
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
                        @if ($isSeller)
                            <template
                                x-if="selectedAppointment.can_review && selectedAppointment.status_key === 'requested'">
                                <div class="space-y-3" x-data="{ rejecting: false }">
                                    <p class="text-xs leading-relaxed text-slate-500">Setujui untuk mengonfirmasi
                                        jadwal
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

                                    <form x-show="rejecting" method="POST"
                                        x-bind:action="selectedAppointment.status_url"
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
                                                            value="{{ $slot }}" class="peer sr-only"
                                                            required>
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

                                        <p class="text-xs leading-relaxed text-slate-500">Jadwal pengganti akan dikirim
                                            ke
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
                                <span x-show="selectedAppointment.can_review">Pengajuan survei ini sudah
                                    diproses.</span>
                                <span x-show="! selectedAppointment.can_review">Anda tidak memiliki akses untuk
                                    meninjau
                                    pengajuan ini.</span>
                            </p>
                        @else
                            <template x-if="selectedAppointment.has_reschedule">
                                <div class="mb-3 space-y-3 rounded-md border border-amber-200 bg-amber-50/60 p-3">
                                    <p class="text-xs leading-relaxed text-slate-600">
                                        Penjual mengusulkan jadwal pengganti. Setujui untuk langsung mengonfirmasi
                                        jadwal
                                        baru, atau tolak saran tersebut.
                                    </p>
                                    <div class="flex flex-col gap-2 sm:flex-row">
                                        <form method="POST" x-bind:action="selectedAppointment.reschedule_url"
                                            class="flex-1">
                                            @csrf
                                            <input type="hidden" name="decision" value="accepted">
                                            <button type="submit"
                                                class="inline-flex w-full items-center justify-center rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                                Setujui jadwal
                                            </button>
                                        </form>
                                        <form method="POST" x-bind:action="selectedAppointment.reschedule_url"
                                            class="flex-1">
                                            @csrf
                                            <input type="hidden" name="decision" value="declined">
                                            <button type="submit"
                                                x-on:click="if (! confirm('Tolak saran jadwal pengganti ini?')) $event.preventDefault()"
                                                class="inline-flex w-full items-center justify-center rounded-md border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                                                Tolak saran
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </template>
                        @endif
                        <button type="button" x-on:click="selectedAppointment = null"
                            class="w-full rounded-md bg-secondary px-4 py-2.5 text-sm font-semibold text-white hover:bg-secondary/90 mt-3">Tutup
                            rincian</button>
                    </div>
                </div>
            </template>
        </aside>
    </div>
</div>
