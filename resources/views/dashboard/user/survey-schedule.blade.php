@extends('layouts.app')
@section('page-title', 'Jadwal Survey')

@section('content')
    @php
        $pendingSurveyCount = $appointments->where('status', 'requested')->count();
    @endphp

    <div class="space-y-6">
        <header class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Dashboard pembeli</p>
                <h1 class="mt-1 font-heading text-2xl font-bold tracking-tight text-secondary">Jadwal survei saya</h1>
                <p class="mt-1 text-sm text-slate-500">Pantau jadwal kunjungan properti yang Anda ajukan kepada penjual.</p>
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

        @if ($errors->has('reschedule'))
            <div role="alert"
                class="flex items-start gap-2.5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                <svg class="mt-0.5 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ $errors->first('reschedule') }}</span>
            </div>
        @endif

        <x-survey-calendar
            :month="$month"
            :calendar-days="$calendarDays"
            :appointments-by-date="$appointmentsByDate"
            :bookings="$calendarBookings"
            :appointments="$appointments"
            perspective="buyer"
            reset-route="buyer.survey-schedule"
            title="Kalender jadwal survei"
            description="Pilih avatar penjual untuk membuka rincian survei."
        />

        @if ($rescheduleRequests->isNotEmpty())
            <x-card-dashboard title="Persetujuan jadwal pengganti"
                subtitle="Penjual mengusulkan jadwal baru untuk survei Anda. Setujui untuk langsung mengonfirmasi jadwal tersebut, atau tolak saran.">
                <x-slot name="actions">
                    <span
                        class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                        {{ $rescheduleRequests->count() }} menunggu persetujuan
                    </span>
                </x-slot>

                <x-table>
                    <x-slot name="head">
                        <x-table.cell>Properti</x-table.cell>
                        <x-table.cell>Jadwal awal</x-table.cell>
                        <x-table.cell>Saran jadwal baru</x-table.cell>
                        <x-table.cell>Aksi</x-table.cell>
                    </x-slot>

                    @foreach ($rescheduleRequests as $rescheduleRequest)
                        <x-table.row striped>
                            <x-table.data>
                                <span class="font-bold text-secondary">{{ $rescheduleRequest->property->title }}</span>
                                <span class="mt-1 block text-xs text-slate-500">{{ $rescheduleRequest->property->city }},
                                    {{ $rescheduleRequest->property->province }}</span>
                            </x-table.data>
                            <x-table.data>
                                {{ $rescheduleRequest->scheduled_at->locale('id')->translatedFormat('d M Y') }}
                                <span
                                    class="mt-1 block text-xs text-slate-500">{{ $rescheduleRequest->scheduled_at->format('H:i') }}
                                    WIB</span>
                            </x-table.data>
                            <x-table.data>
                                <span
                                    class="font-bold text-primary">{{ $rescheduleRequest->reschedule_suggested_at->locale('id')->translatedFormat('d M Y') }}</span>
                                <span
                                    class="mt-1 block text-xs text-slate-500">{{ $rescheduleRequest->reschedule_suggested_at->format('H:i') }}
                                    WIB</span>
                            </x-table.data>
                            <x-table.data>
                                <div class="flex flex-wrap gap-2">
                                    <form method="POST"
                                        action="{{ route('buyer.survey-schedule.reschedule', $rescheduleRequest) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="accepted">
                                        <button type="submit"
                                            class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700">
                                            Setujui jadwal
                                        </button>
                                    </form>
                                    <form method="POST"
                                        action="{{ route('buyer.survey-schedule.reschedule', $rescheduleRequest) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="declined">
                                        <button type="submit"
                                            class="inline-flex items-center justify-center rounded-md border border-rose-200 bg-white px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-50">
                                            Tolak saran
                                        </button>
                                    </form>
                                </div>
                            </x-table.data>
                        </x-table.row>
                    @endforeach
                </x-table>
            </x-card-dashboard>
        @endif

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
    </div>
@endsection
