@extends('layouts.app')
@section('page-title', 'Jadwal Survey')

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

    <div class="space-y-6" x-data="{ bookings: @js($calendarBookings), statusStyles: @js($bookingStatusStyles), selectedAppointment: null, openBooking(id) { this.selectedAppointment = this.bookings.find((booking) => booking.id === id) }, statusStyle(statusKey) { return this.statusStyles[statusKey] ?? this.statusStyles.cancelled } }" x-on:keydown.escape.window="selectedAppointment = null">
        <header class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Dashboard pembeli</p>
                <h1 class="mt-1 font-heading text-2xl font-bold tracking-tight text-secondary">Jadwal survei saya</h1>
                <p class="mt-1 text-sm text-slate-500">Pantau jadwal kunjungan properti yang Anda ajukan.</p>
            </div>
            <p class="text-xs text-slate-500">
                {{ $appointments->count() }} survei bulan ini
                @if ($pendingSurveyCount > 0)
                    <span class="font-semibold text-amber-600">· {{ $pendingSurveyCount }} menunggu persetujuan</span>
                @endif
            </p>
        </header>

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
                    <a href="{{ route('buyer.survey-schedule') }}"
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
                                        <span class="text-[10px] font-medium text-slate-400">{{ $dayAppointments->count() }}
                                            jadwal</span>
                                    @endif
                                </div>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach ($dayAppointments as $appointment)
                                        @php($statusStyle = $bookingStatusStyles[$appointment->status] ?? $bookingStatusStyles['cancelled'])
                                        <button type="button" x-on:click="openBooking({{ $appointment->id }})"
                                            title="{{ $appointment->seller->name }} · {{ $appointment->scheduled_at->format('H:i') }} · {{ $appointment->property->title }} · {{ $statusStyle['label'] }}"
                                            aria-label="Buka survei {{ $appointment->seller->name }}, {{ $appointment->scheduled_at->format('H:i') }}, {{ $appointment->property->title }}, status {{ $statusStyle['label'] }}"
                                            class="relative size-8 rounded-full border-2 border-white ring-1 transition hover:scale-110 focus:outline-none focus:ring-2 focus:ring-primary {{ $statusStyle['ring'] }}">
                                            <img src="{{ $appointment->seller->avatar_url }}"
                                                alt="{{ $appointment->seller->name }}"
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
                <span class="text-slate-400">{{ $appointments->where('status', 'confirmed')->count() }} disetujui ·
                    {{ $pendingSurveyCount }} menunggu persetujuan</span>
            </footer>
        </section>

        <x-card-dashboard title="Jadwal survei mendatang"
            subtitle="Survei properti yang akan datang atau menunggu persetujuan">
            <x-slot name="actions">
                <span
                    class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-600">
                    {{ $upcomingSurveys->count() }} jadwal
                </span>
            </x-slot>

            <x-table>
                <x-slot name="head">
                    <x-table.cell>Properti</x-table.cell>
                    <x-table.cell>Penjual</x-table.cell>
                    <x-table.cell>Jadwal</x-table.cell>
                    <x-table.cell>Status</x-table.cell>
                </x-slot>

                @forelse ($upcomingSurveys as $survey)
                    <x-table.row striped>
                        <x-table.data>
                            <span class="font-bold text-secondary">{{ $survey->property->title }}</span>
                            <span class="mt-1 block text-xs text-slate-500">{{ $survey->property->city }},
                                {{ $survey->property->province }}</span>
                        </x-table.data>
                        <x-table.data>{{ $survey->seller->name }}</x-table.data>
                        <x-table.data>{{ $survey->scheduled_at->locale('id')->translatedFormat('d M Y') }}
                            <span class="mt-1 block text-xs text-slate-500">{{ $survey->scheduled_at->format('H:i') }}
                                WIB</span>
                        </x-table.data>
                        <x-table.data>
                            <x-appointment-status-badge :status="$survey->status" />
                        </x-table.data>
                    </x-table.row>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center">
                            <p class="font-heading font-bold text-slate-500">Belum ada jadwal survei mendatang</p>
                            <p class="mt-1 text-sm text-slate-400">Jadwal survei berikutnya akan tampil di sini.</p>
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </x-card-dashboard>

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
                                <p class="text-xs font-semibold uppercase text-primary">Rincian jadwal</p>
                                <h2 id="booking-drawer-title" class="mt-1 text-lg font-bold text-secondary"
                                    x-text="selectedAppointment.seller_name"></h2>
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
                                    x-bind:alt="selectedAppointment.seller_name"
                                    class="size-14 rounded-full object-cover ring-1 ring-slate-200">
                                <div>
                                    <p class="font-semibold text-slate-800" x-text="selectedAppointment.seller_name"></p>
                                    <p class="mt-1 text-xs text-slate-500">Penjual properti</p>
                                </div>
                            </div>
                            <dl class="divide-y divide-slate-100 rounded-md border border-slate-200">
                                <div class="px-4 py-3">
                                    <dt class="text-xs text-slate-500">Tanggal dan waktu</dt>
                                    <dd class="mt-1 text-sm font-medium text-slate-800"><span
                                            x-text="selectedAppointment.date"></span>, pukul <span
                                            x-text="selectedAppointment.time"></span> WIB</dd>
                                </div>
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
                            <button type="button" x-on:click="selectedAppointment = null"
                                class="w-full rounded-md bg-secondary px-4 py-2.5 text-sm font-semibold text-white hover:bg-secondary/90">Tutup
                                rincian</button>
                        </div>
                    </div>
                </template>
            </aside>
        </div>
    </div>
@endsection
