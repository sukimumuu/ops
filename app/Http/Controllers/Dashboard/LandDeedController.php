<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Payments\DpAmountCalculator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LandDeedController extends Controller
{
    /**
     * Page 1: Dashboard Antrian
     */
    public function index(Request $request): View
    {
        $ppat = $request->user();
        abort_unless($ppat instanceof User && $ppat->can('appointments.view_all'), 403);

        $validated = $request->validate([
            'month' => ['sometimes', 'date_format:Y-m'],
        ]);
        $month = Carbon::createFromFormat('!Y-m', $validated['month'] ?? now()->format('Y-m'));
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();

        $ppatMeetings = Appointment::query()
            ->whereHas('property', fn (Builder $query): Builder => $query->where('assigned_ppat_id', $ppat->id))
            ->where('status', Appointment::STATUS_COMPLETED)
            ->where('buyer_intent', Appointment::BUYER_INTENT_DEAL)
            ->whereNotNull('ppat_meeting_status')
            ->whereBetween('ppat_meeting_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->with([
                'buyer:id,name,email,phone,avatar_path',
                'seller:id,name',
                'property:id,title,address,city,district,certificate_type,url_maps,assigned_ppat_id',
            ])
            ->get()
            ->map(function (Appointment $appointment): Appointment {
                $calendarAppointment = clone $appointment;
                $calendarAppointment->setAttribute(
                    'scheduled_at',
                    $appointment->ppat_meeting_date->copy()->startOfDay(),
                );
                $calendarAppointment->setAttribute('calendar_event_id', 'ppat-meeting-'.$appointment->id);
                $calendarAppointment->setAttribute('calendar_event_type', 'ppat_meeting');

                return $calendarAppointment;
            });

        $calendarAppointments = $ppatMeetings->sortBy('scheduled_at')->values();

        $calendarBookings = $calendarAppointments->map(function (Appointment $appointment) use ($ppat): array {
            $isPpatMeeting = $appointment->calendar_event_type === 'ppat_meeting';

            return [
                'id' => $appointment->calendar_event_id,
                'event_type' => $appointment->calendar_event_type,
                'counterpart_name' => $appointment->buyer->name,
                'counterpart_email' => $appointment->buyer->email,
                'counterpart_phone' => $appointment->buyer->phone,
                'avatar_url' => $appointment->buyer->avatar_url,
                'seller_name' => $appointment->seller->name,
                'date' => $appointment->scheduled_at->locale('id')->translatedFormat('l, d F Y'),
                'time' => $isPpatMeeting ? null : $appointment->scheduled_at->format('H:i'),
                'reschedule_date' => $appointment->reschedule_suggested_at?->locale('id')->translatedFormat('l, d F Y'),
                'reschedule_time' => $appointment->reschedule_suggested_at?->format('H:i'),
                'status_key' => $isPpatMeeting
                    ? 'ppat_meeting_'.$appointment->ppat_meeting_status
                    : $appointment->status,
                'status' => $isPpatMeeting
                    ? $appointment->ppatMeetingStatusLabel()
                    : $appointment->statusLabel(),
                'buyer_intent_key' => $appointment->buyer_intent,
                'buyer_intent_label' => $appointment->buyerIntentLabel(),
                'result_notes' => $appointment->result_notes,
                'result_recorded_at' => $appointment->completed_at
                    ? $appointment->completed_at->locale('id')->translatedFormat('l, d F Y, H:i').' WIB'
                    : null,
                'can_review' => ! $isPpatMeeting && $ppat->can('review', $appointment),
                'can_manage_meeting' => $isPpatMeeting
                    && $appointment->ppat_meeting_status === Appointment::PPAT_MEETING_STATUS_REQUESTED
                    && $ppat->can('managePpatMeeting', $appointment),
                'status_url' => route('land-deed.appointments.status', $appointment),
                'ppat_meeting_url' => $isPpatMeeting
                    ? route('land-deed.ppat-meetings.update', $appointment)
                    : null,
                'ppat_meeting_status' => $isPpatMeeting ? $appointment->ppat_meeting_status : null,
                'notes' => $appointment->notes,
                'property_title' => $appointment->property->title,
                'property_certificate' => $appointment->property->certificate_type,
                'property_address' => $appointment->property->address,
                'property_location' => $appointment->property->district.', '.$appointment->property->city,
                'property_maps_url' => $appointment->property->url_maps,
            ];
        })->values();

        $appointmentsByDate = $calendarAppointments->groupBy(
            fn (Appointment $appointment): string => $appointment->scheduled_at->toDateString(),
        );
        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY);
        $calendarDays = CarbonPeriod::create($calendarStart, $calendarEnd);

        return view('dashboard.land-deed.index', compact(
            'ppatMeetings',
            'appointmentsByDate',
            'calendarBookings',
            'calendarDays',
            'month',
        ));
    }

    /**
     * Page 2: Pengecekan BPN
     */
    public function bpnCheck(): View
    {
        $metrics = [
            'waiting' => [
                'title' => 'Menunggu',
                'count' => 3,
                'unit' => 'berkas',
                'description' => 'Siap untuk diajukan',
                'descriptionClass' => 'text-slate-500',
                'icon' => 'clock',
            ],
            'in_progress' => [
                'title' => 'Dalam Proses',
                'count' => 2,
                'unit' => 'berkas',
                'description' => 'Menunggu hasil dari BPN',
                'descriptionClass' => 'text-slate-500',
                'icon' => 'refresh',
            ],
            'needs_repair' => [
                'title' => 'Perlu Perbaikan',
                'count' => 1,
                'unit' => 'berkas',
                'description' => 'Prioritaskan tindak lanjut',
                'descriptionClass' => 'text-rose-600 font-medium',
                'icon' => 'alert',
            ],
            'completed' => [
                'title' => 'Selesai',
                'count' => 18,
                'unit' => 'berkas',
                'description' => 'Hasil pengecekan sudah dicatat',
                'descriptionClass' => 'text-emerald-600 font-medium',
                'icon' => 'check-circle',
            ],
        ];

        $items = [
            [
                'trx_id' => 'TRX-2026-0152',
                'trx_type' => 'Jual beli',
                'client' => 'Rina Kartika',
                'cert_title' => 'SHM 08231 · Pondok Aren',
                'cert_desc' => 'Jl. Merpati No. 12 - 120 m²',
                'date' => '16 Okt 2026',
                'time' => '08.20 WIB',
                'status' => 'Menunggu',
                'status_variant' => 'amber',
                'follow_up' => 'Berkas siap diajukan',
                'follow_up_sub' => 'Petugas: Nabila Putri',
                'follow_up_sub_class' => 'text-slate-500',
                'action' => 'Proses',
            ],
            [
                'trx_id' => 'TRX-2026-0151',
                'trx_type' => 'Jual beli',
                'client' => 'Budi Santoso',
                'cert_title' => 'SHM 04108 · Serpong',
                'cert_desc' => 'Jl. Anggrek No. 8 - 180 m²',
                'date' => '15 Okt 2026',
                'time' => '15.00 WIB',
                'status' => 'Dalam Proses',
                'status_variant' => 'blue',
                'follow_up' => 'Menunggu hasil BPN',
                'follow_up_sub' => 'Ref. PK-2026-0871',
                'follow_up_sub_class' => 'text-slate-500',
                'action' => 'Detail',
            ],
            [
                'trx_id' => 'TRX-2026-0150',
                'trx_type' => 'Hibah',
                'client' => 'Dewi Lestari',
                'cert_title' => 'SHM 06742 · Pamulang',
                'cert_desc' => 'Jl. Kenanga No. 21 - 150 m²',
                'date' => '14 Okt 2026',
                'time' => '11.10 WIB',
                'status' => 'Perlu Perbaikan',
                'status_variant' => 'rose',
                'follow_up' => 'Hasil pindai SHM kurang jelas',
                'follow_up_sub' => 'Minta dokumen pengganti hari ini',
                'follow_up_sub_class' => 'text-rose-600',
                'action' => 'Perbaiki',
            ],
            [
                'trx_id' => 'TRX-2026-0149',
                'trx_type' => 'Jual beli',
                'client' => 'Ahmad Fauzi',
                'cert_title' => 'SHM 03216 · Ciputat',
                'cert_desc' => 'Jl. Melati No. 6 - 96 m²',
                'date' => '13 Okt 2026',
                'time' => '10.00 WIB',
                'status' => 'Selesai',
                'status_variant' => 'emerald',
                'follow_up' => 'Hasil pengecekan diterima',
                'follow_up_sub' => '15 Okt 2026 · PK-2026-0864',
                'follow_up_sub_class' => 'text-slate-500',
                'action' => 'Lihat hasil',
            ],
            [
                'trx_id' => 'TRX-2026-0148',
                'trx_type' => 'Jual beli',
                'client' => 'Siti Rahmawati',
                'cert_title' => 'SHM 09125 · Pondok Aren',
                'cert_desc' => 'Jl. Bintaro No. 9 - 200 m²',
                'date' => '12 Okt 2026',
                'time' => '14.00 WIB',
                'status' => 'Selesai',
                'status_variant' => 'emerald',
                'follow_up' => 'Hasil pengecekan diterima',
                'follow_up_sub' => '14 Okt 2026 · PK-2026-0860',
                'follow_up_sub_class' => 'text-slate-500',
                'action' => 'Lihat hasil',
            ],
            [
                'trx_id' => 'TRX-2026-0147',
                'trx_type' => 'Jual beli',
                'client' => 'Hendra Wijaya',
                'cert_title' => 'SHM 05280 · Serpong',
                'cert_desc' => 'Jl. Kencana No. 4 - 144 m²',
                'date' => '09 Okt 2026',
                'time' => '13.00 WIB',
                'status' => 'Selesai',
                'status_variant' => 'emerald',
                'follow_up' => 'Hasil pengecekan diterima',
                'follow_up_sub' => '13 Okt 2026 · PK-2026-0852',
                'follow_up_sub_class' => 'text-slate-500',
                'action' => 'Lihat hasil',
            ],
        ];

        return view('dashboard.land-deed.bpn-check', compact('metrics', 'items'));
    }

    /**
     * Page 3: Validasi Pajak
     */
    public function taxValidation(): View
    {
        $metrics = [
            'total' => [
                'title' => 'Total Berkas Pajak',
                'count' => 18,
                'unit' => 'berkas',
                'description' => 'Berkas telah melewati pengecekan BPN',
                'descriptionClass' => 'text-slate-500',
                'icon' => 'document',
            ],
            'incomplete' => [
                'title' => 'Belum Selesai',
                'count' => 5,
                'unit' => 'berkas',
                'description' => '3 menunggu · 1 proses · 1 belum lengkap',
                'descriptionClass' => 'text-slate-500',
                'icon' => 'clock',
            ],
            'need_action' => [
                'title' => 'Perlu Dilengkapi',
                'count' => 1,
                'unit' => 'berkas',
                'description' => 'Bukti setor PPh belum tersedia',
                'descriptionClass' => 'text-rose-600 font-medium',
                'icon' => 'alert',
            ],
            'recorded' => [
                'title' => 'Validasi Tercatat',
                'count' => 13,
                'unit' => 'berkas',
                'description' => 'Bukti dari otoritas telah diarsipkan',
                'descriptionClass' => 'text-emerald-600 font-medium',
                'icon' => 'check-circle',
            ],
        ];

        $items = [
            [
                'trx_id' => 'TRX-2026-0149',
                'cert_info' => 'SHM 03216 · Ciputat',
                'client' => 'Ahmad Fauzi',
                'bphtb' => 'BPHTB: SSPD tersedia',
                'pph' => 'PPh: bukti setor belum diunggah',
                'completeness' => 'Belum Lengkap',
                'completeness_variant' => 'rose',
                'doc_count' => '1 dari 2 dokumen tersedia',
                'status' => 'Perlu Dilengkapi',
                'status_variant' => 'rose',
                'status_sub' => null,
                'updated_at' => '16 Okt 2026',
                'updated_time' => '08.10 WIB · Nabila',
                'action' => 'Persyaratan',
            ],
            [
                'trx_id' => 'TRX-2026-0148',
                'cert_info' => 'SHM 09125 · Pondok Aren',
                'client' => 'Siti Rahmawati',
                'bphtb' => 'BPHTB: SSPD tersedia',
                'pph' => 'PPh: bukti setor tersedia',
                'completeness' => 'Lengkap',
                'completeness_variant' => 'emerald',
                'doc_count' => '2 dari 2 dokumen tersedia',
                'status' => 'Menunggu',
                'status_variant' => 'amber',
                'status_sub' => null,
                'updated_at' => '15 Okt 2026',
                'updated_time' => '15.40 WIB · Nabila',
                'action' => 'Detail',
            ],
            [
                'trx_id' => 'TRX-2026-0147',
                'cert_info' => 'SHM 05280 · Serpong',
                'client' => 'Hendra Wijaya',
                'bphtb' => 'BPHTB: SSPD tersedia',
                'pph' => 'PPh: bukti setor tersedia',
                'completeness' => 'Lengkap',
                'completeness_variant' => 'emerald',
                'doc_count' => '2 dari 2 dokumen tersedia',
                'status' => 'Dalam Proses',
                'status_variant' => 'blue',
                'status_sub' => null,
                'updated_at' => '15 Okt 2026',
                'updated_time' => '11.20 WIB · Rizal',
                'action' => 'Detail',
            ],
            [
                'trx_id' => 'TRX-2026-0146',
                'cert_info' => 'SHM 07316 · Pamulang',
                'client' => 'Ratna Puspita',
                'bphtb' => 'BPHTB: SSPD tersedia',
                'pph' => 'PPh: bukti setor tersedia',
                'completeness' => 'Lengkap',
                'completeness_variant' => 'emerald',
                'doc_count' => '2 dari 2 dokumen tersedia',
                'status' => 'Validasi Tercatat',
                'status_variant' => 'emerald',
                'status_sub' => 'Bukti otoritas diarsipkan',
                'updated_at' => '14 Okt 2026',
                'updated_time' => '14.15 WIB · Rizal',
                'action' => 'Lihat bukti',
            ],
            [
                'trx_id' => 'TRX-2026-0145',
                'cert_info' => 'SHM 02489 · Ciputat',
                'client' => 'Dimas Saputra',
                'bphtb' => 'BPHTB: SSPD tersedia',
                'pph' => 'PPh: bukti setor tersedia',
                'completeness' => 'Lengkap',
                'completeness_variant' => 'emerald',
                'doc_count' => '2 dari 2 dokumen tersedia',
                'status' => 'Validasi Tercatat',
                'status_variant' => 'emerald',
                'status_sub' => 'Bukti otoritas diarsipkan',
                'updated_at' => '13 Okt 2026',
                'updated_time' => '16.00 WIB · Nabila',
                'action' => 'Lihat bukti',
            ],
            [
                'trx_id' => 'TRX-2026-0144',
                'cert_info' => 'SHM 06139 · Serpong',
                'client' => 'Maya Anggraini',
                'bphtb' => 'BPHTB: SSPD tersedia',
                'pph' => 'PPh: bukti setor tersedia',
                'completeness' => 'Lengkap',
                'completeness_variant' => 'emerald',
                'doc_count' => '2 dari 2 dokumen tersedia',
                'status' => 'Validasi Tercatat',
                'status_variant' => 'emerald',
                'status_sub' => 'Bukti otoritas diarsipkan',
                'updated_at' => '12 Okt 2026',
                'updated_time' => '10.30 WIB · Rizal',
                'action' => 'Lihat bukti',
            ],
        ];

        return view('dashboard.land-deed.tax-validation', compact('metrics', 'items'));
    }

    /**
     * Page 4: Jadwal Penandatanganan AJB
     */
    public function ajbSigning(): View
    {
        $metrics = [
            'upcoming' => [
                'title' => 'Jadwal Mendatang',
                'count' => 7,
                'unit' => 'janji',
                'description' => '16–20 Oktober 2026',
                'descriptionClass' => 'text-slate-500',
                'icon' => 'calendar',
            ],
            'today' => [
                'title' => 'Jadwal Hari Ini',
                'count' => 3,
                'unit' => 'janji',
                'description' => 'Mulai pukul 09.00 WIB',
                'descriptionClass' => 'text-slate-500',
                'icon' => 'calendar-check',
            ],
            'confirmed' => [
                'title' => 'Terkonfirmasi',
                'count' => 5,
                'unit' => 'janji',
                'description' => '2 lainnya menunggu konfirmasi',
                'descriptionClass' => 'text-slate-500',
                'icon' => 'users',
            ],
            'conflicts' => [
                'title' => 'Konflik Jadwal',
                'count' => 1,
                'unit' => 'konflik',
                'description' => '2 janji bertumpang tindih hari ini',
                'descriptionClass' => 'text-rose-600 font-medium',
                'icon' => 'alert',
            ],
        ];

        $items = [
            [
                'date' => '16 Okt 2026',
                'date_sub' => 'Jumat · hari ini',
                'time' => '09.00–09.45',
                'location' => 'WIB · Ruang Akad',
                'parties' => 'Sri Wahyuni → Yudi Hartono',
                'parties_role' => 'Penjual → pembeli',
                'trx_id' => 'TRX-2026-0141',
                'property' => 'SHM 03387 · Ciputat',
                'ppat' => 'Andini Prameswari',
                'readiness' => 'PPAT · 2 saksi dijadwalkan',
                'readiness_class' => 'text-slate-500',
                'status' => 'Terkonfirmasi',
                'status_variant' => 'emerald',
                'conflict' => null,
                'action' => 'Kelola',
            ],
            [
                'date' => '16 Okt 2026',
                'date_sub' => 'Jumat · hari ini',
                'time' => '10.00–11.00',
                'location' => 'WIB · Ruang Akad',
                'parties' => 'Andi Pratama → Citra Dewi',
                'parties_role' => 'Penjual → pembeli',
                'trx_id' => 'TRX-2026-0142',
                'property' => 'SHM 01245 · Pondok Aren',
                'ppat' => 'Andini Prameswari',
                'readiness' => 'PPAT · ruang bertumpang tindih',
                'readiness_class' => 'text-rose-600 font-medium',
                'status' => 'Terkonfirmasi',
                'status_variant' => 'rose',
                'conflict' => 'Bentrok: 10.30–11.00',
                'action' => 'Atur ulang',
            ],
            [
                'date' => '16 Okt 2026',
                'date_sub' => 'Jumat · hari ini',
                'time' => '10.30–11.30',
                'location' => 'WIB · Ruang Akad',
                'parties' => 'Agus Setiawan → Nina Larasati',
                'parties_role' => 'Penjual → pembeli',
                'trx_id' => 'TRX-2026-0143',
                'property' => 'SHM 04862 · Pondok Aren',
                'ppat' => 'Andini Prameswari',
                'readiness' => 'PPAT · ruang bertumpang tindih',
                'readiness_class' => 'text-rose-600 font-medium',
                'status' => 'Menunggu Konfirmasi',
                'status_variant' => 'amber',
                'conflict' => 'Bentrok: 10.30–11.00',
                'action' => 'Atur ulang',
            ],
            [
                'date' => '19 Okt 2026',
                'date_sub' => 'Senin',
                'time' => '09.00–10.00',
                'location' => 'WIB · Ruang Akad',
                'parties' => 'Maya Anggraini → Reza Aditya',
                'parties_role' => 'Penjual → pembeli',
                'trx_id' => 'TRX-2026-0144',
                'property' => 'SHM 06139 · Serpong',
                'ppat' => 'Andini Prameswari',
                'readiness' => 'PPAT · 2 saksi dijadwalkan',
                'readiness_class' => 'text-slate-500',
                'status' => 'Terkonfirmasi',
                'status_variant' => 'emerald',
                'conflict' => null,
                'action' => 'Kelola',
            ],
            [
                'date' => '19 Okt 2026',
                'date_sub' => 'Senin',
                'time' => '11.00–12.00',
                'location' => 'WIB · Ruang Akad',
                'parties' => 'Dimas Saputra → Putri Amalia',
                'parties_role' => 'Penjual → pembeli',
                'trx_id' => 'TRX-2026-0145',
                'property' => 'SHM 02489 · Ciputat',
                'ppat' => 'Andini Prameswari',
                'readiness' => 'PPAT · 2 saksi dijadwalkan',
                'readiness_class' => 'text-slate-500',
                'status' => 'Terkonfirmasi',
                'status_variant' => 'emerald',
                'conflict' => null,
                'action' => 'Kelola',
            ],
            [
                'date' => '20 Okt 2026',
                'date_sub' => 'Selasa',
                'time' => '09.00–10.00',
                'location' => 'WIB · Ruang Akad',
                'parties' => 'Ratna Puspita → Adi Kurniawan',
                'parties_role' => 'Penjual → pembeli',
                'trx_id' => 'TRX-2026-0146',
                'property' => 'SHM 07316 · Pamulang',
                'ppat' => 'Andini Prameswari',
                'readiness' => 'PPAT · 2 saksi dijadwalkan',
                'readiness_class' => 'text-slate-500',
                'status' => 'Terkonfirmasi',
                'status_variant' => 'emerald',
                'conflict' => null,
                'action' => 'Kelola',
            ],
            [
                'date' => '20 Okt 2026',
                'date_sub' => 'Selasa',
                'time' => '13.00–14.00',
                'location' => 'WIB · Ruang Akad',
                'parties' => 'Fajar Nugroho → Laila Safitri',
                'parties_role' => 'Penjual → pembeli',
                'trx_id' => 'TRX-2026-0140',
                'property' => 'SHM 02814 · Serpong',
                'ppat' => 'Andini Prameswari',
                'readiness' => 'PPAT · menunggu pihak pembeli',
                'readiness_class' => 'text-slate-500',
                'status' => 'Menunggu Konfirmasi',
                'status_variant' => 'amber',
                'conflict' => null,
                'action' => 'Kelola',
            ],
        ];

        return view('dashboard.land-deed.ajb-signing', compact('metrics', 'items'));
    }

    /**
     * Page 5: Manajemen Akta
     */
    public function deedManagement(): View
    {
        $metrics = [
            'draft' => [
                'title' => 'Draft',
                'count' => 2,
                'unit' => 'berkas',
                'description' => 'Belum masuk penyusunan',
                'descriptionClass' => 'text-slate-500',
                'icon' => 'edit',
            ],
            'drafting' => [
                'title' => 'Dalam Penyusunan',
                'count' => 2,
                'unit' => 'berkas',
                'description' => 'Sedang disiapkan oleh petugas',
                'descriptionClass' => 'text-slate-500',
                'icon' => 'document',
            ],
            'waiting_sign' => [
                'title' => 'Menunggu Tanda Tangan',
                'count' => 3,
                'unit' => 'berkas',
                'description' => '2 jadwal perlu ditata ulang',
                'descriptionClass' => 'text-slate-500',
                'icon' => 'pen-tool',
            ],
            'signed' => [
                'title' => 'Ditandatangani',
                'count' => 6,
                'unit' => 'berkas',
                'description' => 'Dokumen tersimpan di arsip kantor',
                'descriptionClass' => 'text-emerald-600 font-medium',
                'icon' => 'check-circle',
            ],
        ];

        $items = [
            [
                'code' => 'DRAF-2026-0146',
                'type_desc' => 'AJB · TRX-2026-0146',
                'parties' => 'Ratna Puspita → Adi Kurniawan',
                'parties_role' => 'Penjual → pembeli',
                'property' => 'SHM 07316 · Pamulang',
                'property_sub' => 'Jl. Flamboyan No. 17 · 160 m²',
                'created_date' => '15 Okt 2026',
                'status' => 'Draft',
                'status_variant' => 'slate',
                'officer' => 'Nabila Putri',
                'officer_note' => 'Jadwal: 20 Okt, 09.00',
                'officer_note_class' => 'text-slate-500',
                'action' => 'Edit draft',
            ],
            [
                'code' => 'DRAF-2026-0145',
                'type_desc' => 'AJB · TRX-2026-0145',
                'parties' => 'Dimas Saputra → Putri Amalia',
                'parties_role' => 'Penjual → pembeli',
                'property' => 'SHM 02489 · Ciputat',
                'property_sub' => 'Jl. Cendana No. 10 · 110 m²',
                'created_date' => '14 Okt 2026',
                'status' => 'Dalam Penyusunan',
                'status_variant' => 'blue',
                'officer' => 'Rizal Firmansyah',
                'officer_note' => 'Jadwal: 19 Okt, 11.00',
                'officer_note_class' => 'text-slate-500',
                'action' => 'Lanjutkan',
            ],
            [
                'code' => 'DRAF-2026-0144',
                'type_desc' => 'AJB · TRX-2026-0144',
                'parties' => 'Maya Anggraini → Reza Aditya',
                'parties_role' => 'Penjual → pembeli',
                'property' => 'SHM 06139 · Serpong',
                'property_sub' => 'Jl. Jelupang No. 15 · 135 m²',
                'created_date' => '13 Okt 2026',
                'status' => 'Menunggu Tanda Tangan',
                'status_variant' => 'amber',
                'officer' => 'Nabila Putri',
                'officer_note' => 'Jadwal: 19 Okt, 09.00',
                'officer_note_class' => 'text-slate-500',
                'action' => 'Detail',
            ],
            [
                'code' => 'DRAF-2026-0143',
                'type_desc' => 'AJB · TRX-2026-0143',
                'parties' => 'Agus Setiawan → Nina Larasati',
                'parties_role' => 'Penjual → pembeli',
                'property' => 'SHM 04862 · Pondok Aren',
                'property_sub' => 'Jl. Pondok Jaya No. 3 · 180 m²',
                'created_date' => '12 Okt 2026',
                'status' => 'Menunggu Tanda Tangan',
                'status_variant' => 'amber',
                'officer' => 'Rizal Firmansyah',
                'officer_note' => 'Jadwal bentrok · perlu diatur ulang',
                'officer_note_class' => 'text-rose-600 font-medium',
                'action' => 'Detail',
            ],
            [
                'code' => 'DRAF-2026-0142',
                'type_desc' => 'AJB · TRX-2026-0142',
                'parties' => 'Andi Pratama → Citra Dewi',
                'parties_role' => 'Penjual → pembeli',
                'property' => 'SHM 01245 · Pondok Aren',
                'property_sub' => 'Jl. Bintaro Blok H No. 12 · 120 m²',
                'created_date' => '09 Okt 2026',
                'status' => 'Menunggu Tanda Tangan',
                'status_variant' => 'amber',
                'officer' => 'Nabila Putri',
                'officer_note' => 'Jadwal bentrok · perlu diatur ulang',
                'officer_note_class' => 'text-rose-600 font-medium',
                'action' => 'Detail',
            ],
            [
                'code' => '125/2026',
                'type_desc' => 'AJB · TRX-2026-0139',
                'parties' => 'Nur Aisyah → Bayu Permana',
                'parties_role' => 'Penjual → pembeli',
                'property' => 'SHM 01973 · Pamulang',
                'property_sub' => 'Jl. Salak No. 9 · 140 m²',
                'created_date' => '07 Okt 2026',
                'status' => 'Ditandatangani',
                'status_variant' => 'emerald',
                'officer' => 'Rizal Firmansyah',
                'officer_note' => 'Ditandatangani: 13 Okt 2026',
                'officer_note_class' => 'text-slate-500',
                'action' => 'Lihat akta',
            ],
        ];

        return view('dashboard.land-deed.deed-management', compact('metrics', 'items'));
    }

    /**
     * Page 6: Resi Balik Nama
     */
    public function transferForOwnership(): View
    {
        $metrics = [
            'received' => [
                'title' => 'Berkas Diterima',
                'count' => 1,
                'unit' => 'berkas',
                'description' => 'Resi pengajuan telah diarsipkan',
                'descriptionClass' => 'text-slate-500',
                'icon' => 'inbox',
            ],
            'examination' => [
                'title' => 'Pemeriksaan Berkas',
                'count' => 2,
                'unit' => 'berkas',
                'description' => 'Menunggu tindak lanjut BPN',
                'descriptionClass' => 'text-slate-500',
                'icon' => 'document',
            ],
            'registration' => [
                'title' => 'Proses Pencatatan',
                'count' => 2,
                'unit' => 'berkas',
                'description' => 'Dalam proses di Kantor Pertanahan',
                'descriptionClass' => 'text-slate-500',
                'icon' => 'clipboard',
            ],
            'completed' => [
                'title' => 'Selesai dari BPN',
                'count' => 1,
                'unit' => 'berkas',
                'description' => 'Dokumen hasil diterima kantor',
                'descriptionClass' => 'text-emerald-600 font-medium',
                'icon' => 'check-circle',
            ],
        ];

        $items = [
            [
                'receipt_no' => 'BN-2026-0068',
                'bpn_ref' => 'BPN: 18432/2026',
                'trx_ref' => 'TRX-2026-0139 · AJB 125/2026',
                'property' => 'SHM 01973 · Pamulang',
                'candidate_name' => 'Bayu Permana',
                'client_name' => 'Klien: Nur Aisyah',
                'date' => '15 Okt 2026',
                'progress' => 'Berkas Diterima',
                'progress_variant' => 'slate',
                'progress_sub' => 'Resi pengajuan diarsipkan',
                'updated_at' => '15 Okt 2026',
                'updated_time' => '14.30 WIB',
                'action' => 'Detail resi',
            ],
            [
                'receipt_no' => 'BN-2026-0067',
                'bpn_ref' => 'BPN: 18301/2026',
                'trx_ref' => 'TRX-2026-0138 · AJB 124/2026',
                'property' => 'SHM 06418 · Ciputat',
                'candidate_name' => 'Indah Sari',
                'client_name' => 'Klien: Bambang Susilo',
                'date' => '14 Okt 2026',
                'progress' => 'Pemeriksaan Berkas',
                'progress_variant' => 'blue',
                'progress_sub' => 'Menunggu pemeriksaan BPN',
                'updated_at' => '15 Okt 2026',
                'updated_time' => '10.15 WIB',
                'action' => 'Detail resi',
            ],
            [
                'receipt_no' => 'BN-2026-0066',
                'bpn_ref' => 'BPN: 18045/2026',
                'trx_ref' => 'TRX-2026-0137 · AJB 123/2026',
                'property' => 'SHM 04561 · Pondok Aren',
                'candidate_name' => 'Arief Wibowo',
                'client_name' => 'Klien: Lilis Suryani',
                'date' => '12 Okt 2026',
                'progress' => 'Pemeriksaan Berkas',
                'progress_variant' => 'blue',
                'progress_sub' => 'Tidak ada kekurangan tercatat',
                'updated_at' => '14 Okt 2026',
                'updated_time' => '13.40 WIB',
                'action' => 'Detail resi',
            ],
            [
                'receipt_no' => 'BN-2026-0065',
                'bpn_ref' => 'BPN: 17862/2026',
                'trx_ref' => 'TRX-2026-0136 · AJB 122/2026',
                'property' => 'SHM 07822 · Serpong',
                'candidate_name' => 'Dian Pertiwi',
                'client_name' => 'Klien: Rizky Maulana',
                'date' => '09 Okt 2026',
                'progress' => 'Proses Pencatatan',
                'progress_variant' => 'purple',
                'progress_sub' => 'Sesuai pembaruan dari BPN',
                'updated_at' => '15 Okt 2026',
                'updated_time' => '09.20 WIB',
                'action' => 'Detail resi',
            ],
            [
                'receipt_no' => 'BN-2026-0064',
                'bpn_ref' => 'BPN: 17718/2026',
                'trx_ref' => 'TRX-2026-0135 · AJB 121/2026',
                'property' => 'SHM 01654 · Pamulang',
                'candidate_name' => 'Rudi Hermawan',
                'client_name' => 'Klien: Wati Handayani',
                'date' => '08 Okt 2026',
                'progress' => 'Proses Pencatatan',
                'progress_variant' => 'purple',
                'progress_sub' => 'Sesuai pembaruan dari BPN',
                'updated_at' => '14 Okt 2026',
                'updated_time' => '15.10 WIB',
                'action' => 'Detail resi',
            ],
            [
                'receipt_no' => 'BN-2026-0063',
                'bpn_ref' => 'BPN: 17589/2026',
                'trx_ref' => 'TRX-2026-0134 · AJB 120/2026',
                'property' => 'SHM 03602 · Ciputat',
                'candidate_name' => 'Sari Kusuma',
                'client_name' => 'Klien: Eko Prasetyo',
                'date' => '07 Okt 2026',
                'progress' => 'Selesai dari BPN',
                'progress_variant' => 'emerald',
                'progress_sub' => 'Dokumen hasil diterima kantor',
                'updated_at' => '15 Okt 2026',
                'updated_time' => '16.00 WIB',
                'action' => 'Lihat hasil',
            ],
        ];

        return view('dashboard.land-deed.transfer-for-ownership', compact('metrics', 'items'));
    }

    /**
     * Page: Daftar properti yang ditugaskan ke PPAT.
     */
    public function clientProperties(Request $request): View
    {
        $ppat = $request->user();
        abort_unless($ppat instanceof User && $ppat->can('appointments.view_all'), 403);

        $statusOptions = [
            'draft' => 'Draft',
            'pending_verification' => 'Menunggu Verifikasi',
            'published' => 'Terbit',
            'reserved' => 'Dipesan',
            'sold' => 'Terjual',
            'archived' => 'Diarsipkan',
            'rejected' => 'Ditolak',
        ];
        $categoryOptions = [
            'sale' => 'Dijual',
            'rent' => 'Disewakan',
        ];
        $propertyTypeOptions = [
            'bangunan' => 'Bangunan',
            'tanah' => 'Tanah',
        ];
        $sortOptions = [
            'newest' => 'Terbaru',
            'oldest' => 'Terlama',
            'price_asc' => 'Harga Terendah',
            'price_desc' => 'Harga Tertinggi',
        ];

        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        $category = (string) $request->query('category', '');
        $propertyType = (string) $request->query('property_type', '');
        $sort = (string) $request->query('sort', 'newest');

        $status = array_key_exists($status, $statusOptions) ? $status : '';
        $category = array_key_exists($category, $categoryOptions) ? $category : '';
        $propertyType = array_key_exists($propertyType, $propertyTypeOptions) ? $propertyType : '';
        $sort = array_key_exists($sort, $sortOptions) ? $sort : 'newest';

        $properties = Property::query()
            ->where('assigned_ppat_id', $ppat->id)
            ->with(['photos' => fn ($query) => $query->limit(1)])
            ->withExists([
                'appointments as has_requested_survey' => fn (Builder $query): Builder => $query->where('status', Appointment::STATUS_REQUESTED),
                'appointments as has_confirmed_survey' => fn (Builder $query): Builder => $query->where('status', Appointment::STATUS_CONFIRMED),
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $term = '%'.$search.'%';

                $query->where(function (Builder $query) use ($term): void {
                    $query->where('title', 'like', $term)
                        ->orWhere('address', 'like', $term)
                        ->orWhere('city', 'like', $term)
                        ->orWhere('district', 'like', $term)
                        ->orWhere('province', 'like', $term);
                });
            })
            ->when($status !== '', fn (Builder $query): Builder => $query->where('status', $status))
            ->when($category !== '', fn (Builder $query): Builder => $query->where('category', $category))
            ->when($propertyType !== '', fn (Builder $query): Builder => $query->where('property_type', $propertyType));

        $totalProperties = (clone $properties)->count();

        match ($sort) {
            'oldest' => $properties->orderBy('created_at')->orderBy('id'),
            'price_asc' => $properties->orderBy('price')->orderByDesc('id'),
            'price_desc' => $properties->orderByDesc('price')->orderByDesc('id'),
            default => $properties->orderByDesc('created_at')->orderByDesc('id'),
        };

        $properties = $properties->paginate(12)->withQueryString();

        $properties->getCollection()->transform(function (Property $property): Property {
            $property->setAttribute('image_url', $property->primaryPhotoUrl());
            $property->setAttribute('survey_status', match (true) {
                (bool) $property->has_requested_survey => Appointment::STATUS_REQUESTED,
                (bool) $property->has_confirmed_survey => Appointment::STATUS_CONFIRMED,
                default => null,
            });

            return $property;
        });

        $hasFilters = $search !== '' || $status !== '' || $category !== '' || $propertyType !== '';

        return view('dashboard.land-deed.client-properties', compact(
            'properties',
            'totalProperties',
            'hasFilters',
            'search',
            'status',
            'category',
            'propertyType',
            'sort',
            'statusOptions',
            'categoryOptions',
            'propertyTypeOptions',
            'sortOptions',
        ));
    }

    /**
     * Page: Detail properti klien untuk PPAT.
     */
    public function clientDetailProperty(
        Request $request,
        Property $property,
        DpAmountCalculator $amountCalculator,
    ): View {
        $ppat = $request->user();

        abort_unless(
            $ppat instanceof User && $property->assigned_ppat_id === $ppat->id,
            403,
            'Anda tidak memiliki akses ke properti klien ini.',
        );

        $property->load([
            'photos',
            'documents.validatedBy:id,name',
            'assignedPpat:id,name,email,phone,avatar_path',
            'seller:id,name,email,phone,avatar_path',
        ]);

        $appointments = $property->appointments()
            ->with([
                'buyer:id,name,email,phone,avatar_path',
                'seller:id,name,email,phone,avatar_path',
            ])
            ->orderByDesc('scheduled_at')
            ->orderByDesc('id')
            ->get();

        $transaction = $property->transactions()
            ->with(['buyer:id,name,email,phone,avatar_path'])
            ->latest('id')
            ->first();

        $dpPaymentAttempt = $transaction?->dpPaymentAttempts()->latest('id')->first();
        $requiredDpAmount = $transaction?->state === 'draft'
            ? $amountCalculator->calculate($transaction->total_property_amount)
            : null;

        $latestAppointment = $appointments->first();
        $pendingPpatMeetingRequests = $appointments
            ->filter(fn (Appointment $appointment): bool => $appointment->status === Appointment::STATUS_COMPLETED
                && $appointment->buyer_intent === Appointment::BUYER_INTENT_DEAL
                && $appointment->ppat_meeting_status === Appointment::PPAT_MEETING_STATUS_REQUESTED
                && $appointment->ppat_meeting_date !== null)
            ->values();
        $selectedAppointment = $appointments->firstWhere('id', $request->integer('appointment'))
            ?? $pendingPpatMeetingRequests->first()
            ?? $latestAppointment;
        $canManagePpatMeeting = $pendingPpatMeetingRequests->isNotEmpty()
            && $ppat->can('managePpatMeeting', $pendingPpatMeetingRequests->first());

        $progress = $latestAppointment?->progressState() ?? [
            'label' => 'Belum ada pengajuan survei',
            'description' => 'Progres survei akan muncul setelah calon pembeli mengajukan jadwal kunjungan.',
            'percentage' => 0,
            'tone' => 'slate',
            'completed_steps' => 0,
            'stopped' => true,
        ];

        if ($latestAppointment) {
            if ($latestAppointment->hasPendingReschedule()) {
                $progress['description'] = 'Penjual menyarankan jadwal pengganti. Menunggu tanggapan calon pembeli.';
            } elseif ($latestAppointment->status === Appointment::STATUS_REQUESTED) {
                $progress['description'] = 'Permintaan jadwal survei klien menunggu persetujuan penjual.';
            } elseif ($latestAppointment->status === Appointment::STATUS_CONFIRMED) {
                $progress['description'] = 'Jadwal survei klien telah disetujui penjual.';
            } elseif ($latestAppointment->status === Appointment::STATUS_COMPLETED) {
                $progress['description'] = 'Survei klien telah selesai dilaksanakan dan hasil telah dicatat.';
            }
        }

        $property->setAttribute('image_url', $property->primaryPhotoUrl());

        $mapLocation = filled($property->latitude) && filled($property->longitude)
            ? "{$property->latitude},{$property->longitude}"
            : trim("{$property->address}, {$property->district}, {$property->city}, {$property->province}");
        $mapUrl = filled($property->url_maps)
            ? $property->url_maps
            : 'https://www.google.com/maps/search/?api=1&query='.urlencode($mapLocation);

        $surveyStats = [
            'total' => $appointments->count(),
            'completed' => $appointments->where('status', Appointment::STATUS_COMPLETED)->count(),
            'confirmed' => $appointments->where('status', Appointment::STATUS_CONFIRMED)->count(),
            'pending' => $appointments->where('status', Appointment::STATUS_REQUESTED)->count(),
        ];

        $clientBuyer = $transaction?->buyer ?? $latestAppointment?->buyer;
        $bpnProgress = $property->bpnVerificationProgress();
        $isStageThreeUnlocked = $transaction?->hasCompletedDownPayment() ?? false;

        return view('dashboard.land-deed.client-detail-properties', compact(
            'property',
            'appointments',
            'latestAppointment',
            'selectedAppointment',
            'pendingPpatMeetingRequests',
            'canManagePpatMeeting',
            'transaction',
            'dpPaymentAttempt',
            'requiredDpAmount',
            'progress',
            'mapUrl',
            'surveyStats',
            'clientBuyer',
            'bpnProgress',
            'isStageThreeUnlocked',
        ));
    }

    /**
     * Page: Pengecekan Legalitas BPN & Validasi Pajak.
     */
    public function bpnLegalCheck(Request $request, Property $property): View
    {
        $ppat = $request->user();

        abort_unless(
            $ppat instanceof User && $property->assigned_ppat_id === $ppat->id,
            403,
            'Anda tidak memiliki akses ke properti klien ini.',
        );

        $property->load([
            'assignedPpat:id,name,email,phone,avatar_path',
            'seller:id,name,email,phone,avatar_path',
        ]);

        $documents = $property->documents()
            ->with('validatedBy:id,name')
            ->get();

        $transaction = $property->transactions()
            ->with(['buyer:id,name,email'])
            ->latest('id')
            ->first();

        abort_unless(
            $transaction?->hasCompletedDownPayment(),
            403,
            'Pengecekan BPN belum tersedia sebelum pembayaran DP berhasil dikonfirmasi.',
        );

        $metrics = [
            'total' => [
                'title' => 'Total Dokumen',
                'count' => $documents->count(),
                'unit' => 'dokumen',
                'description' => 'Dokumen legal yang diunggah penjual',
                'descriptionClass' => 'text-slate-500',
            ],
            'pending' => [
                'title' => 'Menunggu Validasi',
                'count' => $documents->where('validation_status', PropertyPhoto::VALIDATION_PENDING)->count(),
                'unit' => 'dokumen',
                'description' => 'Belum ditinjau PPAT',
                'descriptionClass' => 'text-slate-500',
            ],
            'approved' => [
                'title' => 'Disetujui',
                'count' => $documents->where('validation_status', PropertyPhoto::VALIDATION_APPROVED)->count(),
                'unit' => 'dokumen',
                'description' => 'Lolos pengecekan legalitas',
                'descriptionClass' => 'text-emerald-600 font-medium',
            ],
            'rejected' => [
                'title' => 'Ditolak',
                'count' => $documents->where('validation_status', PropertyPhoto::VALIDATION_REJECTED)->count(),
                'unit' => 'dokumen',
                'description' => 'Perlu perbaikan dari penjual',
                'descriptionClass' => 'text-rose-600 font-medium',
            ],
        ];

        $canReview = $ppat->can('documents.verify_status');

        return view('dashboard.land-deed.bpn-legal-check', compact(
            'property',
            'documents',
            'transaction',
            'metrics',
            'canReview',
        ));
    }

    /**
     * Review a legal document and advance the transaction's BPN state.
     */
    public function reviewDocument(Request $request, Property $property, PropertyPhoto $document): RedirectResponse
    {
        abort_unless($document->property_id === $property->id, 404);

        $ppat = $request->user();

        abort_unless(
            $ppat instanceof User && $property->assigned_ppat_id === $ppat->id,
            403,
            'Anda tidak memiliki akses ke dokumen properti ini.',
        );

        abort_unless(
            $property->transactions()->latest('id')->first()?->hasCompletedDownPayment(),
            403,
            'Validasi dokumen belum tersedia sebelum pembayaran DP berhasil dikonfirmasi.',
        );

        if ($document->isApproved()) {
            return back()->withErrors([
                'validation_status' => 'Dokumen yang telah disetujui bersifat final dan tidak dapat diubah.',
            ]);
        }

        $validated = $request->validate([
            'validation_status' => [
                'required',
                Rule::in([PropertyPhoto::VALIDATION_APPROVED, PropertyPhoto::VALIDATION_REJECTED]),
            ],
            'validation_notes' => ['nullable', 'string', 'max:2000', 'required_if:validation_status,rejected'],
        ]);

        DB::transaction(function () use ($property, $document, $ppat, $validated): void {
            $document->forceFill([
                'validation_status' => $validated['validation_status'],
                'validation_notes' => $validated['validation_notes'] ?? null,
                'validated_by_id' => $ppat->id,
                'validated_at' => now(),
            ])->save();

            $transaction = $property->transactions()->latest('id')->first();

            if (! $transaction instanceof Transaction) {
                return;
            }

            $currentState = (string) $transaction->state;

            if (in_array($currentState, ['dp_paid', 'held_in_escrow'], true)) {
                $this->moveTransactionTo(
                    $transaction,
                    'bpn_checking',
                    $ppat,
                    'Pengecekan legalitas BPN dimulai oleh PPAT.',
                );
                $currentState = 'bpn_checking';
            }

            $allDocumentsApproved = $property->documents()
                ->get()
                ->every(fn (PropertyPhoto $document): bool => $document->isApproved());

            if ($allDocumentsApproved && in_array($currentState, ['dp_paid', 'held_in_escrow', 'bpn_checking'], true)) {
                $this->moveTransactionTo(
                    $transaction,
                    'bpn_cleared',
                    $ppat,
                    'Seluruh dokumen legal disetujui. Pengecekan BPN selesai.',
                );
            } elseif (! $allDocumentsApproved && $currentState === 'bpn_cleared') {
                $this->moveTransactionTo(
                    $transaction,
                    'bpn_checking',
                    $ppat,
                    'Terdapat dokumen yang ditolak setelah BPN selesai. Pengecekan dibuka kembali.',
                );
            }
        });

        $message = $validated['validation_status'] === PropertyPhoto::VALIDATION_APPROVED
            ? 'Dokumen berhasil disetujui.'
            : 'Dokumen ditolak dan menunggu perbaikan.';

        return back()->with('status', $message);
    }

    private function moveTransactionTo(
        Transaction $transaction,
        string $toState,
        User $actor,
        string $notes,
    ): void {
        $fromState = (string) $transaction->state;

        if ($fromState === $toState) {
            return;
        }

        $transaction->forceFill(['state' => $toState])->save();

        $transaction->logs()->create([
            'actor_id' => $actor->id,
            'from_state' => $fromState,
            'to_state' => $toState,
            'notes' => $notes,
        ]);
    }
}
