<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LandDeedController extends Controller
{
    /**
     * Page 1: Dashboard Antrian
     */
    public function index(Request $request): View
    {
        $ppat = $request->user();
        abort_unless($ppat instanceof User && $ppat->hasRole('PPAT'), 403);

        $validated = $request->validate([
            'month' => ['sometimes', 'date_format:Y-m'],
        ]);
        $month = Carbon::createFromFormat('!Y-m', $validated['month'] ?? now()->format('Y-m'));
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();

        $appointments = Appointment::query()
            ->whereHas('property', fn (Builder $query): Builder => $query->where('assigned_ppat_id', $ppat->id))
            ->whereBetween('scheduled_at', [$monthStart, $monthEnd])
            ->with([
                'buyer:id,name,avatar_path',
                'seller:id,name',
                'property:id,title,address,city,district,certificate_type,assigned_ppat_id',
            ])
            ->orderBy('scheduled_at')
            ->get();

        $calendarBookings = $appointments->map(fn (Appointment $appointment): array => [
            'id' => $appointment->id,
            'client_name' => $appointment->buyer->name,
            'avatar_url' => $appointment->buyer->avatar_url,
            'date' => $appointment->scheduled_at->locale('id')->translatedFormat('l, d F Y'),
            'time' => $appointment->scheduled_at->format('H:i'),
            'status_key' => $appointment->status,
            'status' => $appointment->statusLabel(),
            'can_review' => $ppat->can('review', $appointment),
            'status_url' => route('land-deed.appointments.status', $appointment),
            'notes' => $appointment->notes,
            'property_title' => $appointment->property->title,
            'property_certificate' => $appointment->property->certificate_type,
            'property_address' => $appointment->property->address,
            'property_location' => $appointment->property->district.', '.$appointment->property->city,
            'seller_name' => $appointment->seller->name,
        ])->values();

        $appointmentsByDate = $appointments->groupBy(fn (Appointment $appointment): string => $appointment->scheduled_at->toDateString());
        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY);
        $calendarDays = CarbonPeriod::create($calendarStart, $calendarEnd);

        return view('dashboard.land-deed.index', compact(
            'appointments',
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
}
