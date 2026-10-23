@extends('layouts.app')
@section('page-title', 'Permintaan Survei Masuk')
@section('content')
    <div class="space-y-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Dashboard seller</p>
                <h1 class="mt-1 font-heading text-2xl font-bold tracking-tight text-secondary">Permintaan survei masuk</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">
                    Tinjau permintaan kunjungan dari calon pembeli, lalu setujui atau tolak jadwalnya.
                </p>
            </div>
            @if ($pendingSurveyCount > 0)
                <span
                    class="inline-flex w-fit items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3.5 py-2 text-xs font-bold text-amber-800">
                    <span class="size-2 rounded-full bg-amber-500" aria-hidden="true"></span>
                    {{ $pendingSurveyCount }} menunggu persetujuan
                </span>
            @endif
        </header>

        @if (session('status'))
            <div role="status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->has('status'))
            <div role="alert" class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                {{ $errors->first('status') }}
            </div>
        @endif

        <x-survey-calendar
            :month="$month"
            :calendar-days="$calendarDays"
            :appointments-by-date="$appointmentsByDate"
            :bookings="$calendarBookings"
            :appointments="$calendarAppointments"
            perspective="seller"
            reset-route="seller.request-survey"
            title="Kalender permintaan survei"
            description="Pilih avatar calon pembeli untuk menyetujui, menolak, atau menawarkan jadwal pengganti."
        />

        <x-card-dashboard title="Daftar permintaan survei"
            subtitle="Permintaan dari calon pembeli untuk properti yang Anda tayangkan">
            <x-table>
                <x-slot name="head">
                    <x-table.cell>Properti</x-table.cell>
                    <x-table.cell>Calon pembeli</x-table.cell>
                    <x-table.cell>Jadwal</x-table.cell>
                    <x-table.cell>Status</x-table.cell>
                    <x-table.cell>Aksi</x-table.cell>
                </x-slot>

                @forelse ($appointments as $appointment)
                    <x-table.row striped>
                        <x-table.data>
                            <span class="font-bold text-secondary">{{ $appointment->property->title }}</span>
                            <span class="mt-1 block text-xs font-normal text-slate-500">
                                {{ $appointment->property->city }}, {{ $appointment->property->province }}
                            </span>
                        </x-table.data>
                        <x-table.data>
                            <span class="font-semibold text-secondary">{{ $appointment->buyer->name }}</span>
                            <span class="mt-1 block text-xs font-normal text-slate-500">{{ $appointment->buyer->email }}</span>
                        </x-table.data>
                        <x-table.data>
                            <span class="font-semibold text-secondary">
                                {{ $appointment->scheduled_at->locale('id')->translatedFormat('d M Y') }}
                            </span>
                            <span class="mt-1 block text-xs font-normal text-slate-500">
                                {{ $appointment->scheduled_at->format('H:i') }} WIB
                            </span>
                        </x-table.data>
                        <x-table.data>
                            <x-appointment-status-badge :status="$appointment->status" />
                        </x-table.data>
                        <x-table.data>
                            @if ($appointment->isAwaitingReview())
                                <a href="#survey-calendar"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary/30">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v13H4V6a1 1 0 0 1 1-1Z" />
                                    </svg>
                                    Tinjau di kalender
                                </a>
                            @else
                                <span class="text-xs font-medium text-slate-400">Sudah ditinjau</span>
                            @endif
                        </x-table.data>
                    </x-table.row>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <p class="font-heading font-bold text-slate-500">Belum ada permintaan survei</p>
                            <p class="mt-1 text-sm text-slate-400">Permintaan dari calon pembeli akan muncul di sini.</p>
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <x-table.pagination :paginator="$appointments" class="mt-4" />
        </x-card-dashboard>
    </div>
@endsection
