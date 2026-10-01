@extends('layouts.app')
@section('page-title', 'Properti Tersimpan')
@section('content')
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filter Kategori & Pencarian -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <!-- Tab Kategori -->
                <div x-data="{ activeTab: 'semua' }" class="flex items-center gap-2 overflow-x-auto pb-2 sm:pb-0 scrollbar-none">
                    <button @click="activeTab = 'semua'"
                        :class="activeTab === 'semua' ? 'bg-primary text-white shadow-sm' :
                            'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-5 py-2 rounded-full text-sm font-semibold transition shrink-0">
                        Semua
                    </button>
                    <button @click="activeTab = 'transaksi'"
                        :class="activeTab === 'transaksi' ? 'bg-primary text-white shadow-sm' :
                            'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-5 py-2 rounded-full text-sm font-semibold transition shrink-0">
                        Transaksi
                    </button>
                    <button @click="activeTab = 'pengguna'"
                        :class="activeTab === 'pengguna' ? 'bg-primary text-white shadow-sm' :
                            'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-5 py-2 rounded-full text-sm font-semibold transition shrink-0">
                        Pengguna
                    </button>
                    <button @click="activeTab = 'properti'"
                        :class="activeTab === 'properti' ? 'bg-primary text-white shadow-sm' :
                            'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-5 py-2 rounded-full text-sm font-semibold transition shrink-0">
                        Properti
                    </button>
                    <button @click="activeTab = 'sistem'"
                        :class="activeTab === 'sistem' ? 'bg-primary text-white shadow-sm' :
                            'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-5 py-2 rounded-full text-sm font-semibold transition shrink-0">
                        Sistem
                    </button>
                </div>

                <!-- Input Pencarian -->
                <div class="relative w-full sm:w-72">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" placeholder="Cari notifikasi..."
                        class="w-full pl-10 pr-4 py-2 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary shadow-sm transition placeholder:text-slate-400">
                </div>
            </div>

            <!-- Kartu Utam List Notifikasi -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">

                <!-- Header Kartu -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <h1 class="text-lg font-bold text-slate-800">Semua Notifikasi</h1>
                        <span
                            class="px-2.5 py-0.5 text-xs font-semibold text-primary bg-orange-50 rounded-full border border-orange-100/60">
                            4 belum dibaca
                        </span>
                    </div>

                    <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                        <span>Tampilkan:</span>
                        <select
                            class="bg-transparent font-semibold text-slate-700 border-none p-0 pr-6 focus:ring-0 cursor-pointer text-xs">
                            <option value="all">Semua status</option>
                            <option value="unread">Belum dibaca</option>
                            <option value="read">Sudah dibaca</option>
                        </select>
                    </div>
                </div>

                <!-- List Notifikasi -->
                <div class="divide-y divide-slate-100">

                    <!-- Item 1: Transaksi (Belum Dibaca) -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between p-5 gap-4 bg-orange-50/20 hover:bg-slate-50/80 transition relative">
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-emerald-100/70 text-emerald-600 rounded-xl shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V8a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2 py-0.5 text-[11px] font-semibold text-emerald-600 bg-emerald-50 rounded-md">Transaksi</span>
                                    <span class="flex items-center gap-1 text-[11px] font-semibold text-primary">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                        Belum dibaca
                                    </span>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">Dana escrow telah diterima</h3>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Pembayaran Rp 6.200.000.000 untuk transaksi TX-2081 berhasil diterima melalui
                                    Midtrans Virtual Account.
                                </p>
                                <p class="text-[11px] text-slate-400 pt-0.5">Hari ini, 14:32 • 2 menit lalu</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                            <a href="#"
                                class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-slate-300 transition shadow-sm">
                                Lihat transaksi
                            </a>
                            <button class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Item 2: Pengguna (Belum Dibaca) -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between p-5 gap-4 bg-orange-50/20 hover:bg-slate-50/80 transition relative">
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-sky-100/70 text-sky-600 rounded-xl shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2 py-0.5 text-[11px] font-semibold text-sky-600 bg-sky-50 rounded-md">Pengguna</span>
                                    <span class="flex items-center gap-1 text-[11px] font-semibold text-primary">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                        Belum dibaca
                                    </span>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">Dokumen KYC menunggu tinjauan</h3>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Rian Hidayat mengunggah KTP dan swafoto baru. Verifikasi diperlukan sebelum akun
                                    dapat bertransaksi.
                                </p>
                                <p class="text-[11px] text-slate-400 pt-0.5">Hari ini, 14:16 • 18 menit lalu</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                            <a href="#"
                                class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-slate-300 transition shadow-sm">
                                Tinjau KYC
                            </a>
                            <button class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Item 3: Properti (Belum Dibaca) -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between p-5 gap-4 bg-orange-50/20 hover:bg-slate-50/80 transition relative">
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-orange-100/70 text-primary rounded-xl shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2 py-0.5 text-[11px] font-semibold text-primary bg-orange-50 rounded-md">Properti</span>
                                    <span class="flex items-center gap-1 text-[11px] font-semibold text-primary">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                        Belum dibaca
                                    </span>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">Listing baru diajukan untuk moderasi</h3>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    PRP-219 • Villa Canggu Berawa diajukan oleh Nusantara Living dan siap ditinjau.
                                </p>
                                <p class="text-[11px] text-slate-400 pt-0.5">Hari ini, 13:52 • 42 menit lalu</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                            <a href="#"
                                class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-slate-300 transition shadow-sm">
                                Moderasi listing
                            </a>
                            <button class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Item 4: Transaksi (Sudah Dibaca) -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between p-5 gap-4 hover:bg-slate-50/80 transition">
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-emerald-100/70 text-emerald-600 rounded-xl shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2 py-0.5 text-[11px] font-semibold text-emerald-600 bg-emerald-50 rounded-md">Transaksi</span>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">Dana escrow berhasil dirilis</h3>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Dana transaksi TX-2080 sebesar Rp 1.850.000.000 telah diteruskan kepada Budi
                                    Hartono.
                                </p>
                                <p class="text-[11px] text-slate-400 pt-0.5">Hari ini, 12:24 • 2 jam lalu</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                            <a href="#"
                                class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-slate-300 transition shadow-sm">
                                Lihat transaksi
                            </a>
                            <button class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Item 5: Sistem (Sudah Dibaca) -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between p-5 gap-4 hover:bg-slate-50/80 transition">
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-purple-100/70 text-purple-600 rounded-xl shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2 py-0.5 text-[11px] font-semibold text-purple-600 bg-purple-50 rounded-md">Sistem</span>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">Pemeliharaan sistem selesai</h3>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Pembaruan koneksi gateway pembayaran selesai. Seluruh layanan kembali beroperasi
                                    normal.
                                </p>
                                <p class="text-[11px] text-slate-400 pt-0.5">Hari ini, 09:30 • 5 jam lalu</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                            <button class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Item 6: Transaksi Dispute (Belum Dibaca) -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between p-5 gap-4 bg-orange-50/20 hover:bg-slate-50/80 transition relative">
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-red-100/70 text-red-500 rounded-xl shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2 py-0.5 text-[11px] font-semibold text-red-600 bg-red-50 rounded-md">Transaksi</span>
                                    <span class="flex items-center gap-1 text-[11px] font-semibold text-primary">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                        Belum dibaca
                                    </span>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">Dispute baru memerlukan tindakan</h3>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Pembeli transaksi TX-2079 mengajukan dispute terkait dokumen serah terima properti.
                                </p>
                                <p class="text-[11px] text-slate-400 pt-0.5">Kemarin, 17:45</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                            <a href="#"
                                class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-slate-300 transition shadow-sm">
                                Tangani dispute
                            </a>
                            <button class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Item 7: Pengguna (Sudah Dibaca) -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between p-5 gap-4 hover:bg-slate-50/80 transition">
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-sky-100/70 text-sky-600 rounded-xl shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2 py-0.5 text-[11px] font-semibold text-sky-600 bg-sky-50 rounded-md">Pengguna</span>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">Akun PPAT baru telah aktif</h3>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Akun Andri Prasetyo berhasil diverifikasi dan memperoleh akses sebagai PPAT.
                                </p>
                                <p class="text-[11px] text-slate-400 pt-0.5">Kemarin, 15:08</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                            <button class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Item 8: Properti Pelanggaran (Sudah Dibaca) -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between p-5 gap-4 hover:bg-slate-50/80 transition">
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-red-100/70 text-red-500 rounded-xl shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2 py-0.5 text-[11px] font-semibold text-primary bg-orange-50 rounded-md">Properti</span>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">Listing ditandai melanggar kebijakan</h3>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    PRP-207 • Ruko Sentra Niaga Kelapa Gading diturunkan setelah pemeriksaan moderasi.
                                </p>
                                <p class="text-[11px] text-slate-400 pt-0.5">Kemarin, 10:42</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                            <a href="#"
                                class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-slate-300 transition shadow-sm">
                                Lihat listing
                            </a>
                            <button class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Footer Pagination -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between px-6 py-4 bg-slate-50/50 border-t border-slate-100 gap-4">
                    <p class="text-xs font-medium text-slate-500">
                        Menampilkan <span class="font-bold text-slate-700">1–8</span> dari <span
                            class="font-bold text-slate-700">38</span> notifikasi
                    </p>

                    <div class="flex items-center gap-1.5 self-center">
                        <button
                            class="px-3 py-1.5 text-xs font-semibold text-slate-400 bg-white border border-slate-200 rounded-lg cursor-not-allowed">
                            Sebelumnya
                        </button>
                        <button class="px-3 py-1.5 text-xs font-bold text-white bg-primary rounded-lg shadow-sm">
                            1
                        </button>
                        <button
                            class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                            2
                        </button>
                        <button
                            class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                            3
                        </button>
                        <button
                            class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition shadow-sm">
                            Selanjutnya
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
