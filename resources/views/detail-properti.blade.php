<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Detail Properti - Mihom">
    <link rel="icon" href="{{ asset('assets/png/favicongray.ico') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" href="{{ asset('assets/png/faviconwhite.ico') }}" media="(prefers-color-scheme: dark)">
    <title>Dijual Rumah Lokasi Strategis di Jatipadang, Jalan Salihara - Mihom</title>
    @vite(['resources/css/app.css', 'resources/css/welcome.css', 'resources/css/detail-properti.css', 'resources/js/app.js'])
</head>
<body class="font-manrope bg-white text-secondary antialiased">
    <nav class="navbar fixed inset-x-0 top-0 z-50 transition-all duration-350" id="navbar">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2 no-underline">
                <img id="logo-black" src="{{ asset('assets/png/gry trnsprn.png') }}" alt="Mihom" class="h-8 transition duration-100 ">
                <span class="nav-logo-text font-display text-[22px] font-extrabold tracking-tight text-grey transition duration-100 -colors">mihom</span>
            </a>
            <div class="hidden items-center gap-2 md:flex">
                <a href="{{ route('home') }}" class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-grey/90 transition hover:bg-grey/15">Beranda</a>
                <a href="{{ route('properti') }}" class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-grey/90 transition hover:bg-grey/15">Properti</a>
                <a href="{{ route('kalkulator-pajak') }}" class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-grey/90 transition hover:bg-grey/15">Kalkulator Pajak</a>
                <a href="#tentang" class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-grey/90 transition hover:bg-grey/15">Tentang Kami</a>
                <a href="#kontak" class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-grey/90 transition hover:bg-grey/15">Kontak</a>
            </div>
            @guest
            <a href="{{ route('login') }}" class="hidden rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-[#df3f05] md:block">Daftar / Masuk</a>
            @endguest
            @auth
            <span class="flex gap-2 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ffffff" class="size-6">
                    <path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" clip-rule="evenodd" />
                </svg>
                <a href="{{ route('dashboard') }}" class="text-sm font-bold md:block">
                    {{ Auth::user()->name }}
                </a>
            </span>
            @endauth
            <button class="nav-hamburger flex h-10 w-10 flex-col items-center justify-center gap-[5px] rounded-lg border-0 bg-transparent p-0 md:hidden" id="hamburgerBtn" aria-label="Buka Menu" aria-expanded="false">
                <span class="hamburger-line h-0.5 w-[22px] rounded bg-white transition"></span><span class="hamburger-line h-0.5 w-[22px] rounded bg-white transition"></span><span class="hamburger-line h-0.5 w-[22px] rounded bg-white transition"></span>
            </button>
        </div>
    </nav>

    <div class="mobile-nav fixed inset-x-0 top-16 z-40 hidden flex-col gap-1 border-b border-gray-100 bg-white/95 p-4 shadow-xl backdrop-blur-md md:hidden" id="mobileNav">
        <a href="{{ route('home') }}" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Beranda</a>
        <a href="{{ route('properti') }}" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Properti</a>
        <a href="{{ route('kalkulator-pajak') }}" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Kalkulator Pajak</a>
        <a href="#tentang" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Tentang Kami</a>
        <a href="#kontak" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Kontak</a>
        <a href="{{ route('login') }}" class="mt-2 rounded-lg bg-primary px-5 py-3 text-center text-sm font-bold text-white">Daftar / Masuk</a>
    </div>

    <main class="pt-16">
        <!-- Image Gallery Section -->
        <section class="relative w-full bg-secondary">
            <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <nav class="mb-4 flex items-center gap-2 text-sm" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="text-white/60 hover:text-white transition">Beranda</a>
                    <span class="text-white/40">/</span>
                    <a href="{{ route('properti') }}" class="text-white/60 hover:text-white transition">Properti</a>
                    <span class="text-white/40">/</span>
                    <span class="text-white font-semibold">Detail</span>
                </nav>

                <!-- Title on top of gallery -->
                <h1 class="font-outfit text-2xl md:text-3xl font-extrabold text-white leading-tight mb-5 max-w-3xl">
                    Dijual Rumah Lokasi Strategis di Jatipadang,<br>Jalan Salihara
                </h1>

                <!-- Image Gallery Grid -->
                <div class="gallery-grid grid gap-2 rounded-2xl overflow-hidden" id="galleryGrid">
                    <!-- Main Image -->
                    <div class="gallery-main relative cursor-pointer group" onclick="openGallery(0)">
                        <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&q=80&auto=format&fit=crop" alt="Rumah tampak depan" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-300"></div>
                        <div class="absolute bottom-4 left-4 flex items-center gap-2">
                            <span class="bg-white/90 backdrop-blur-sm text-secondary text-xs font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Video
                            </span>
                        </div>
                    </div>
                    <!-- Thumbnail 1 (top-right) -->
                    <div class="gallery-thumb-1 relative cursor-pointer group overflow-hidden" onclick="openGallery(1)">
                        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&q=80&auto=format&fit=crop" alt="Interior ruang tamu" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-300"></div>
                    </div>
                    <!-- Thumbnail 2 (bottom-right) -->
                    <div class="gallery-thumb-2 relative cursor-pointer group overflow-hidden" onclick="openGallery(2)">
                        <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=600&q=80&auto=format&fit=crop" alt="Dapur modern" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-300"></div>
                    </div>
                </div>

                <!-- Gallery Action Buttons -->
                <div class="flex items-center justify-between py-4">
                    <div class="flex items-center gap-3">
                        <span class="text-white/60 text-xs font-semibold flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            12 foto
                        </span>
                        <span class="text-white/40">•</span>
                        <span class="text-white/60 text-xs font-semibold">Diperbarui 2 hari lalu</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="flex items-center gap-1.5 bg-white/10 hover:bg-white/20 text-white text-xs font-semibold px-4 py-2 rounded-lg transition" onclick="openGallery(0)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Lihat semua
                        </button>
                        <button class="flex items-center gap-1.5 bg-white/10 hover:bg-white/20 text-white text-xs font-semibold px-4 py-2 rounded-lg transition" id="btnShare">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            Bagikan
                        </button>
                        <button class="flex items-center gap-1.5 bg-white/10 hover:bg-white/20 text-white text-xs font-semibold px-4 py-2 rounded-lg transition" id="btnSave">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content Area -->
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-8">

                <!-- Left Column -->
                <div class="space-y-8">
                    <!-- Price & Status -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="bg-primary/10 text-primary text-[11px] font-extrabold uppercase tracking-wider px-3 py-1 rounded-full">Dijual</span>
                                    <span class="bg-green-50 text-green-600 text-[11px] font-extrabold uppercase tracking-wider px-3 py-1 rounded-full">Terverifikasi</span>
                                </div>
                                <h2 class="text-primary font-extrabold text-3xl md:text-4xl">Rp4,85 Miliar</h2>
                                <p class="text-gray-500 text-sm mt-1">Estimasi cicilan: Rp 31,2 juta/bulan</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 text-gray-400 text-xs font-semibold bg-gray-50 px-3 py-1.5 rounded-lg">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    342 dilihat
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-extrabold uppercase tracking-[0.15em] text-primary">Info Properti</span>
                        </div>
                        <h3 class="font-outfit text-xl font-extrabold text-secondary mb-4">Rumah nyaman di jantung Jakarta Selatan</h3>
                        <div class="prose-detail text-gray-600 text-sm leading-7" id="descriptionText">
                            <p>Rumah ini terletak di Jalan Salihara, Jatipadang, Jakarta Selatan. Lokasi yang strategis dekat dengan pusat kota, fasilitas umum dan akses transportasi umum. Cocok untuk keluarga muda yang ingin tinggal di kawasan yang tenang namun tetap dekat dengan segala kebutuhan.</p>
                            <p class="mt-3">Dibangun dengan material berkualitas tinggi dan desain modern minimalis, rumah ini menawarkan kenyamanan tinggal yang optimal. Setiap ruangan dirancang untuk memaksimalkan pencahayaan alami dan sirkulasi udara yang baik.</p>
                            <div class="hidden" id="descriptionMore">
                                <p class="mt-3">Dengan luas tanah 210 m² dan luas bangunan 185 m², rumah ini memiliki tata ruang yang fungsional dan estetis. Terdapat 4 kamar tidur, 3 kamar mandi, garasi untuk 2 mobil, serta taman depan dan belakang yang asri.</p>
                                <p class="mt-3">Lingkungan sekitar aman dengan keamanan 24 jam dan akses one-gate system. Dekat dengan Mall Cilandak Town Square, RS Fatmawati, dan akses Tol TB Simatupang.</p>
                            </div>
                        </div>
                        <button class="mt-3 text-primary text-sm font-bold hover:underline flex items-center gap-1" onclick="toggleDescription()" id="btnReadMore">
                            Baca selengkapnya
                            <svg class="w-4 h-4 transition-transform" id="readMoreIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>

                    <!-- Property Specs Grid -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                        <h3 class="font-outfit text-xl font-extrabold text-secondary mb-6">Informasi lengkap rumah</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                            <div class="spec-card flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-4 text-center transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">Tipe</span>
                                <span class="text-sm font-extrabold text-secondary">Rumah</span>
                            </div>
                            <div class="spec-card flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-4 text-center transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">Luas Tanah</span>
                                <span class="text-sm font-extrabold text-secondary">210 m²</span>
                            </div>
                            <div class="spec-card flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-4 text-center transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">Luas Bangunan</span>
                                <span class="text-sm font-extrabold text-secondary">185 m²</span>
                            </div>
                            <div class="spec-card flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-4 text-center transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">Sertifikat</span>
                                <span class="text-sm font-extrabold text-secondary">SHM</span>
                            </div>
                            <div class="spec-card flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-4 text-center transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">Kamar Tidur</span>
                                <span class="text-sm font-extrabold text-secondary">4</span>
                            </div>
                            <div class="spec-card flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-4 text-center transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">Kamar Mandi</span>
                                <span class="text-sm font-extrabold text-secondary">3</span>
                            </div>
                            <div class="spec-card flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-4 text-center transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">Daya Listrik</span>
                                <span class="text-sm font-extrabold text-secondary">2200 W</span>
                            </div>
                            <div class="spec-card flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-4 text-center transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">Tahun Bangun</span>
                                <span class="text-sm font-extrabold text-secondary">2022</span>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities / Kenyamanan -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                        <h3 class="font-outfit text-xl font-extrabold text-secondary mb-6">Kenyamanan yang sudah tersedia</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach([
                                ['AC', 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                                ['Kolam Renang', 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9'],
                                ['Keamanan 24 Jam', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                                ['Taman', 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
                                ['Garasi 2 Mobil', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                                ['PDAM', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                                ['Dapur Modern', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                                ['Water Heater', 'M13 10V3L4 14h7v7l9-11h-7z'],
                                ['Internet/WiFi', 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.858 15.355-5.858 21.213 0']
                            ] as [$amenity, $svgPath])
                            <div class="amenity-item flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 p-3 transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-8 h-8 flex-shrink-0 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $svgPath }}"/></svg>
                                </div>
                                <span class="text-sm font-semibold text-secondary">{{ $amenity }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Map Section -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                        <h3 class="font-outfit text-xl font-extrabold text-secondary mb-2">Terhubung ke berbagai destinasi penting</h3>
                        <p class="text-gray-500 text-xs mb-5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-primary" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                            Jl. Salihara No.12, Jatipadang, Jakarta Selatan
                        </p>

                        <div class="grid grid-cols-1 lg:grid-cols-[1.2fr_1fr] gap-6">
                            <!-- Map Embed -->
                            <div class="relative rounded-xl overflow-hidden h-[300px] bg-gray-100">
                                <iframe
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15863.274937697684!2d106.8335!3d-6.2848!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f216a6d12027%3A0x7b8b1cc80ecf0e8c!2sJatipadang%2C%20Pasar%20Minggu%2C%20South%20Jakarta%20City%2C%20Jakarta!5e0!3m2!1sen!2sid!4v1695000000000!5m2!1sen!2sid"
                                    class="absolute inset-0 w-full h-full border-0 rounded-xl"
                                    allowfullscreen=""
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>

                            <!-- Nearby Destinations -->
                            <div>
                                <h4 class="text-sm font-extrabold text-secondary mb-4">Fasilitas di sekitar</h4>
                                <div class="space-y-3">
                                    @foreach([
                                        ['MRT Fatmawati', '0,8 km', '3 menit', 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7'],
                                        ['RS Fatmawati', '1,2 km', '5 menit', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                                        ['Cilandak Town Square', '1,5 km', '7 menit', 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                                        ['SMAN 34 Jakarta', '2,0 km', '8 menit', 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                                        ['Akses Tol TB Simatupang', '2,5 km', '10 menit', 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7']
                                    ] as [$name, $distance, $time, $svgPath])
                                    <div class="flex items-center gap-3 group">
                                        <div class="w-9 h-9 flex-shrink-0 flex items-center justify-center rounded-lg bg-gray-100 group-hover:bg-primary/10 transition">
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-primary transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $svgPath }}"/></svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-secondary">{{ $name }}</p>
                                            <p class="text-xs text-gray-400">{{ $distance }} • {{ $time }} berkendara</p>
                                        </div>
                                        <span class="text-xs font-bold text-primary">{{ $distance }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mortgage Calculator -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                        <h3 class="font-outfit text-xl font-extrabold text-secondary mb-1">Hitung cicilan dari rumah impian Anda</h3>
                        <p class="text-gray-500 text-xs mb-6">Simulasi KPR untuk memudahkan perencanaan keuangan Anda</p>

                        <div class="grid grid-cols-1 lg:grid-cols-[1fr_1fr] gap-8">
                            <!-- Calculator Form -->
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Harga Properti</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">Rp</span>
                                        <input type="text" value="4.850.000.000" class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-12 pr-4 text-sm font-semibold text-secondary outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition" id="mortgagePrice">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Uang Muka (%)</label>
                                        <div class="relative">
                                            <input type="number" value="20" min="0" max="90" class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-4 text-sm font-semibold text-secondary outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition" id="mortgageDown">
                                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">%</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Jangka Waktu</label>
                                        <div class="relative">
                                            <select class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-4 text-sm font-semibold text-secondary outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 appearance-none cursor-pointer transition" id="mortgageTenor">
                                                <option value="10">10 tahun</option>
                                                <option value="15">15 tahun</option>
                                                <option value="20" selected>20 tahun</option>
                                                <option value="25">25 tahun</option>
                                                <option value="30">30 tahun</option>
                                            </select>
                                            <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Suku Bunga (%)</label>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-400 font-semibold">+ / -</span>
                                        <input type="range" min="3" max="15" value="7.5" step="0.1" class="mortgage-range flex-1" id="mortgageRate">
                                        <span class="text-sm font-bold text-secondary min-w-[50px] text-right" id="rateDisplay">7.5%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Calculator Result -->
                            <div class="flex flex-col justify-center">
                                <div class="bg-secondary rounded-2xl p-6 text-center">
                                    <p class="text-white/60 text-xs font-semibold mb-1">Estimasi cicilan Anda</p>
                                    <p class="text-white font-extrabold text-3xl md:text-4xl mb-1" id="monthlyPayment">Rp31,2 juta</p>
                                    <p class="text-white/50 text-xs mb-5">per bulan</p>
                                    <button class="w-full bg-primary hover:bg-[#df3f05] text-white font-bold py-3 px-6 rounded-xl transition text-sm" onclick="alert('Fitur pengajuan KPR segera hadir!')">
                                        Ajukan KPR Sekarang
                                    </button>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-3 text-center">*Estimasi berdasarkan suku bunga tetap. Hasil aktual dapat berbeda.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Agent / Advisor -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                        <h3 class="font-outfit text-xl font-extrabold text-secondary mb-5">Rekan Penasihat</h3>
                        <div class="flex items-center gap-4">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80&auto=format&fit=crop&crop=face" alt="Agen properti" class="w-16 h-16 rounded-full object-cover ring-2 ring-primary/20 ring-offset-2">
                            <div class="flex-1">
                                <h4 class="text-base font-extrabold text-secondary">Ahmad Surya Pratama</h4>
                                <p class="text-xs text-gray-400">Spesialis Properti Jakarta Selatan</p>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-[11px] font-bold text-yellow-500 flex items-center gap-0.5">
                                        ★ 4.9/5
                                    </span>
                                    <span class="text-gray-300">|</span>
                                    <span class="text-[11px] text-gray-400 font-semibold">127 transaksi</span>
                                    <span class="text-gray-300">|</span>
                                    <span class="text-[11px] text-gray-400 font-semibold">5 tahun pengalaman</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar (Sticky) -->
                <div class="space-y-5">
                    <div class="sticky top-20 space-y-5">
                        <!-- Agent Card -->
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                            <div class="flex items-center gap-3 mb-4">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80&auto=format&fit=crop&crop=face" alt="Agen properti" class="w-12 h-12 rounded-full object-cover ring-2 ring-primary/20">
                                <div>
                                    <h4 class="text-sm font-extrabold text-secondary">Raka Pradipta</h4>
                                    <p class="text-xs text-gray-400">Agen Terverifikasi</p>
                                </div>
                                <span class="ml-auto bg-green-50 text-green-600 text-[10px] font-bold px-2 py-1 rounded-full">Online</span>
                            </div>

                            <a href="#" class="flex items-center justify-center gap-2 w-full bg-primary hover:bg-[#df3f05] text-white font-bold py-3 rounded-xl text-sm transition mb-2.5">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18a8 8 0 01-4.243-1.212l-.3-.18-3.103.92.92-3.103-.18-.3A8 8 0 1112 20z"/></svg>
                                Hubungi via WhatsApp
                            </a>
                            <button class="flex items-center justify-center gap-2 w-full border-2 border-secondary text-secondary hover:bg-secondary hover:text-white font-bold py-3 rounded-xl text-sm transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                Telepon Agen
                            </button>
                        </div>

                        <!-- Schedule Visit Card -->
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                            <h4 class="text-sm font-extrabold text-secondary mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Jadwalkan Kunjungan
                            </h4>
                            <p class="text-xs text-gray-400 mb-4">Pilih tanggal untuk melihat properti ini secara langsung</p>
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <button class="visit-date-btn bg-gray-50 border border-gray-200 rounded-lg py-2 text-center hover:border-primary hover:bg-[#fff5f1] transition active" data-date="today">
                                    <span class="block text-[10px] text-gray-400 font-semibold">Hari ini</span>
                                    <span class="block text-xs font-bold text-secondary">30 Sep</span>
                                </button>
                                <button class="visit-date-btn bg-gray-50 border border-gray-200 rounded-lg py-2 text-center hover:border-primary hover:bg-[#fff5f1] transition" data-date="tomorrow">
                                    <span class="block text-[10px] text-gray-400 font-semibold">Besok</span>
                                    <span class="block text-xs font-bold text-secondary">1 Okt</span>
                                </button>
                                <button class="visit-date-btn bg-gray-50 border border-gray-200 rounded-lg py-2 text-center hover:border-primary hover:bg-[#fff5f1] transition" data-date="dayafter">
                                    <span class="block text-[10px] text-gray-400 font-semibold">Lusa</span>
                                    <span class="block text-xs font-bold text-secondary">2 Okt</span>
                                </button>
                            </div>
                            <button class="w-full bg-secondary hover:bg-secondary/90 text-white font-bold py-3 rounded-xl text-sm transition">
                                Atur Jadwal Kunjungan
                            </button>
                        </div>

                        <!-- Quick Info Card -->
                        <div class="bg-gradient-to-br from-[#fff5f1] to-white rounded-2xl border border-primary/10 p-5">
                            <h4 class="text-sm font-extrabold text-secondary mb-3">Ringkasan Properti</h4>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400 font-semibold">Tipe</span>
                                    <span class="text-xs font-bold text-secondary">Rumah</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400 font-semibold">Lokasi</span>
                                    <span class="text-xs font-bold text-secondary">Jatipadang, Jaksel</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400 font-semibold">Luas Tanah</span>
                                    <span class="text-xs font-bold text-secondary">210 m²</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400 font-semibold">Luas Bangunan</span>
                                    <span class="text-xs font-bold text-secondary">185 m²</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400 font-semibold">Kamar Tidur</span>
                                    <span class="text-xs font-bold text-secondary">4</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400 font-semibold">Kamar Mandi</span>
                                    <span class="text-xs font-bold text-secondary">3</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400 font-semibold">Sertifikat</span>
                                    <span class="text-xs font-bold text-secondary">SHM</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Similar Properties -->
        <section class="bg-gray-50 px-4 py-16 sm:px-6">
            <div class="mx-auto max-w-7xl">
                <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="mb-2 text-xs font-extrabold uppercase tracking-[0.2em] text-primary">Serupa</p>
                        <h2 class="font-outfit text-3xl font-extrabold text-secondary">Pilihan lain yang mungkin Anda suka!</h2>
                    </div>
                    <a href="{{ route('properti') }}" class="rounded-lg border-2 border-primary px-5 py-2.5 text-sm font-bold text-primary transition hover:bg-primary hover:text-white">Lihat Semua</a>
                </div>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['1613490493576-7fde63acd811', 'Rp 4,2 M', 'Aruna Residence', 'Cilandak, Jakarta Selatan', '4 KT • 3 KM • 200 m²'],
                        ['1600585154340-be6161a56a0c', 'Rp 3,8 M', 'Verde Heights', 'Kemang, Jakarta Selatan', '3 KT • 2 KM • 165 m²'],
                        ['1512917774080-9991f1c4c750', 'Rp 5,1 M', 'Nara Townhouse', 'Cipete, Jakarta Selatan', '4 KT • 4 KM • 230 m²'],
                        ['1600566753190-17f0baa2a6c3', 'Rp 6,5 M', 'Kemang Courtyard', 'Pejaten, Jakarta Selatan', '5 KT • 4 KM • 310 m²']
                    ] as [$image, $price, $title, $location, $specs])
                    <article class="similar-card overflow-hidden rounded-2xl bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                        <div class="relative">
                            <img src="https://images.unsplash.com/photo-{{ $image }}?w=500&q=80&auto=format&fit=crop" alt="{{ $title }}" class="h-48 w-full object-cover">
                            <span class="absolute left-3 top-3 rounded-full bg-primary px-3 py-1 text-[11px] font-extrabold uppercase text-white">Dijual</span>
                        </div>
                        <div class="p-4">
                            <p class="mb-1 text-lg font-extrabold text-primary">{{ $price }}</p>
                            <h3 class="mb-2 text-sm font-bold leading-5 text-secondary">{{ $title }}</h3>
                            <p class="mb-3 flex items-center gap-1 text-xs text-gray-400">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                {{ $location }}
                            </p>
                            <div class="flex gap-4 border-t border-gray-100 pt-3 text-xs font-semibold text-gray-500">
                                {{ $specs }}
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- CTA Banner -->
        <section class="relative overflow-hidden px-4 py-20 text-center sm:px-6">
            <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1400&q=80&auto=format&fit=crop" alt="Rumah" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-secondary/85"></div>
            <div class="relative mx-auto max-w-2xl">
                <h2 class="font-outfit text-3xl font-extrabold text-white sm:text-4xl">Siap melihat rumah ini secara langsung?</h2>
                <p class="mx-auto mt-4 max-w-lg leading-7 text-white/70">Jadwalkan kunjungan bersama agen kami dan temukan rumah impian Anda sekarang juga.</p>
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="#" class="inline-flex items-center gap-2 rounded-xl bg-primary px-8 py-4 text-base font-extrabold text-white transition hover:bg-[#df3f05]">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
                        Hubungi WhatsApp
                    </a>
                    <a href="#" class="inline-flex items-center gap-2 rounded-xl border-2 border-white/30 px-8 py-4 text-base font-extrabold text-white transition hover:bg-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Jadwalkan Kunjungan
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer Component -->
    <x-footer />

    <!-- Fullscreen Gallery Modal -->
    <div class="gallery-modal fixed inset-0 z-[100] hidden bg-black/95 backdrop-blur-md" id="galleryModal">
        <button class="absolute top-5 right-5 z-10 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition" onclick="closeGallery()">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="absolute top-5 left-5 text-white/60 text-sm font-semibold">
            <span id="galleryCounter">1 / 5</span>
        </div>
        <div class="flex items-center justify-center h-full px-16">
            <button class="gallery-nav-btn absolute left-4 z-10 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition" onclick="prevImage()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <img id="galleryImage" src="" alt="Gallery" class="max-h-[85vh] max-w-[85vw] object-contain rounded-lg">
            <button class="gallery-nav-btn absolute right-4 z-10 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition" onclick="nextImage()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    <script>
        // === Navbar scroll ===
        const navbar = document.getElementById('navbar');
        const blackLogo = document.getElementById('logo-black');
        const whiteLogo = document.getElementById('logo-white');
        const hamburger = document.getElementById('hamburgerBtn');
        const mobileNav = document.getElementById('mobileNav');
        window.addEventListener('scroll', () => { 
            const scrolled = window.scrollY > 60; 
            navbar.classList.toggle('scrolled', scrolled); 
        });
        function closeMobileNav() { mobileNav.classList.remove('open'); hamburger.classList.remove('open'); hamburger.setAttribute('aria-expanded', 'false'); }
        hamburger.addEventListener('click', () => { const open = mobileNav.classList.toggle('open'); hamburger.classList.toggle('open', open); hamburger.setAttribute('aria-expanded', String(open)); });
        document.addEventListener('click', (event) => { if (!navbar.contains(event.target) && !mobileNav.contains(event.target)) closeMobileNav(); });

        // === Description toggle ===
        function toggleDescription() {
            const more = document.getElementById('descriptionMore');
            const btn = document.getElementById('btnReadMore');
            const icon = document.getElementById('readMoreIcon');
            const isHidden = more.classList.contains('hidden');
            more.classList.toggle('hidden');
            btn.childNodes[0].textContent = isHidden ? 'Tutup ' : 'Baca selengkapnya ';
            icon.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
        }

        // === Gallery ===
        const galleryImages = [
            'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1400&q=90&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1400&q=90&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1400&q=90&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=1400&q=90&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1400&q=90&auto=format&fit=crop'
        ];
        let currentImage = 0;

        function openGallery(index) {
            currentImage = index;
            updateGallery();
            document.getElementById('galleryModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeGallery() {
            document.getElementById('galleryModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function nextImage() {
            currentImage = (currentImage + 1) % galleryImages.length;
            updateGallery();
        }

        function prevImage() {
            currentImage = (currentImage - 1 + galleryImages.length) % galleryImages.length;
            updateGallery();
        }

        function updateGallery() {
            document.getElementById('galleryImage').src = galleryImages[currentImage];
            document.getElementById('galleryCounter').textContent = `${currentImage + 1} / ${galleryImages.length}`;
        }

        document.getElementById('galleryModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('galleryModal') || e.target.closest('.gallery-nav-btn') === null && e.target.tagName !== 'IMG' && e.target.closest('button') === null) {
                closeGallery();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (!document.getElementById('galleryModal').classList.contains('hidden')) {
                if (e.key === 'Escape') closeGallery();
                if (e.key === 'ArrowRight') nextImage();
                if (e.key === 'ArrowLeft') prevImage();
            }
        });

        // === Mortgage Calculator ===
        const rateSlider = document.getElementById('mortgageRate');
        const rateDisplay = document.getElementById('rateDisplay');
        const monthlyPaymentEl = document.getElementById('monthlyPayment');

        function calculateMortgage() {
            const priceStr = document.getElementById('mortgagePrice').value.replace(/\./g, '').replace(/,/g, '');
            const price = parseFloat(priceStr) || 4850000000;
            const downPercent = parseFloat(document.getElementById('mortgageDown').value) || 20;
            const tenor = parseInt(document.getElementById('mortgageTenor').value) || 20;
            const rate = parseFloat(rateSlider.value) || 7.5;

            rateDisplay.textContent = rate + '%';

            const loanAmount = price * (1 - downPercent / 100);
            const monthlyRate = rate / 100 / 12;
            const totalMonths = tenor * 12;

            let monthly;
            if (monthlyRate === 0) {
                monthly = loanAmount / totalMonths;
            } else {
                monthly = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, totalMonths)) / (Math.pow(1 + monthlyRate, totalMonths) - 1);
            }

            const formatted = (monthly / 1000000).toFixed(1);
            monthlyPaymentEl.textContent = `Rp${formatted} juta`;
        }

        rateSlider.addEventListener('input', calculateMortgage);
        document.getElementById('mortgageDown').addEventListener('input', calculateMortgage);
        document.getElementById('mortgageTenor').addEventListener('change', calculateMortgage);
        document.getElementById('mortgagePrice').addEventListener('input', calculateMortgage);

        // === Visit date selection ===
        document.querySelectorAll('.visit-date-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.visit-date-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });

        // === Fade-in Observer ===
        const observer = new IntersectionObserver((entries) => entries.forEach((entry) => { if (entry.isIntersecting) entry.target.classList.add('visible'); }), { threshold: 0.12 });
        document.querySelectorAll('.fade-in').forEach((element) => observer.observe(element));
    </script>
</body>
</html>
