<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $property->title }} - {{ $property->city }}, {{ $property->province }}">
    <link rel="icon" href="{{ asset('assets/png/favicongray.ico') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" href="{{ asset('assets/png/faviconwhite.ico') }}" media="(prefers-color-scheme: dark)">
    <title>{{ $property->title }} - Mihom</title>
    @vite(['resources/css/app.css', 'resources/css/welcome.css', 'resources/css/detail-properti.css', 'resources/js/app.js'])
</head>

<body class="font-manrope bg-white text-secondary antialiased">
    <nav class="navbar fixed inset-x-0 top-0 z-50 transition-all duration-350" id="navbar">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2 no-underline">
                <img id="logo-black" src="{{ asset('assets/png/gry trnsprn.png') }}" alt="Mihom"
                    class="h-8 transition duration-100 ">
                <span
                    class="nav-logo-text font-display text-[22px] font-extrabold tracking-tight text-grey transition duration-100 -colors">mihom</span>
            </a>
            <div class="hidden items-center gap-2 md:flex">
                <a href="{{ route('home') }}"
                    class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-grey/90 transition hover:bg-grey/15">Beranda</a>
                <a href="{{ route('properties') }}"
                    class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-grey/90 transition hover:bg-grey/15">Properti</a>
                <a href="{{ route('tax-calculator') }}"
                    class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-grey/90 transition hover:bg-grey/15">Kalkulator
                    Pajak</a>
                <a href="#tentang"
                    class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-grey/90 transition hover:bg-grey/15">Tentang
                    Kami</a>
                <a href="#kontak"
                    class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-grey/90 transition hover:bg-grey/15">Kontak</a>
            </div>
            @guest
                <a href="{{ route('login') }}"
                    class="hidden rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-[#df3f05] md:block">Daftar
                    / Masuk</a>
            @endguest
            @auth
                <span class="flex gap-2 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ffffff" class="size-6">
                        <path fill-rule="evenodd"
                            d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"
                            clip-rule="evenodd" />
                    </svg>
                    <a href="{{ route('dashboard') }}" class="text-sm font-bold md:block">
                        {{ Auth::user()->name }}
                    </a>
                </span>
            @endauth
            <button
                class="nav-hamburger flex h-10 w-10 flex-col items-center justify-center gap-[5px] rounded-lg border-0 bg-transparent p-0 md:hidden"
                id="hamburgerBtn" aria-label="Buka Menu" aria-expanded="false">
                <span class="hamburger-line h-0.5 w-[22px] rounded bg-white transition"></span><span
                    class="hamburger-line h-0.5 w-[22px] rounded bg-white transition"></span><span
                    class="hamburger-line h-0.5 w-[22px] rounded bg-white transition"></span>
            </button>
        </div>
    </nav>

    <div class="mobile-nav fixed inset-x-0 top-16 z-40 hidden flex-col gap-1 border-b border-gray-100 bg-white/95 p-4 shadow-xl backdrop-blur-md md:hidden"
        id="mobileNav">
        <a href="{{ route('home') }}"
            class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary"
            onclick="closeMobileNav()">Beranda</a>
        <a href="{{ route('properties') }}"
            class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary"
            onclick="closeMobileNav()">Properti</a>
        <a href="{{ route('tax-calculator') }}"
            class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary"
            onclick="closeMobileNav()">Kalkulator Pajak</a>
        <a href="#tentang"
            class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary"
            onclick="closeMobileNav()">Tentang Kami</a>
        <a href="#kontak"
            class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary"
            onclick="closeMobileNav()">Kontak</a>
        <a href="{{ route('login') }}"
            class="mt-2 rounded-lg bg-primary px-5 py-3 text-center text-sm font-bold text-white">Daftar / Masuk</a>
    </div>

    <main class="pt-16">
        <!-- Image Gallery Section -->
        <section class="relative w-full bg-secondary">
            <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <nav class="mb-4 flex items-center gap-2 text-sm" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="text-white/60 hover:text-white transition">Beranda</a>
                    <span class="text-white/40">/</span>
                    <a href="{{ route('properties') }}" class="text-white/60 hover:text-white transition">Properti</a>
                    <span class="text-white/40">/</span>
                    <span class="text-white font-semibold">Detail</span>
                </nav>

                <!-- Title on top of gallery -->
                <h1 class="font-outfit text-2xl md:text-3xl font-extrabold text-white leading-tight mb-5 max-w-3xl">
                    {{ $property->title }}
                </h1>

                <!-- Image Gallery Grid -->
                <div class="gallery-grid grid gap-2 rounded-2xl overflow-hidden" id="galleryGrid">
                    <!-- Main Image -->
                    <div class="gallery-main relative bg-gray-200 {{ count($property->photo_urls) ? 'cursor-pointer group' : '' }}"
                        @if (count($property->photo_urls)) onclick="openGallery(0)" @endif>
                        @if (isset($property->photo_urls[0]))
                            <img src="{{ $property->photo_urls[0] }}" alt="Foto properti: {{ $property->title }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                onerror="this.remove()">
                        @else
                            <div class="h-full w-full bg-gray-200"></div>
                        @endif
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-300">
                        </div>
                    </div>
                    <!-- Thumbnail 1 (top-right) -->
                    <div class="gallery-thumb-1 relative {{ isset($property->photo_urls[1]) ? 'cursor-pointer group' : '' }} overflow-hidden bg-gray-200"
                        @if (isset($property->photo_urls[1])) onclick="openGallery(1)" @endif>
                        @if (isset($property->photo_urls[1]))
                            <img src="{{ $property->photo_urls[1] }}" alt="Foto properti: {{ $property->title }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                onerror="this.remove()">
                        @else
                            <div class="h-full w-full bg-gray-200"></div>
                        @endif
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-300">
                        </div>
                    </div>
                    <!-- Thumbnail 2 (bottom-right) -->
                    <div class="gallery-thumb-2 relative {{ isset($property->photo_urls[2]) ? 'cursor-pointer group' : '' }} overflow-hidden bg-gray-200"
                        @if (isset($property->photo_urls[2])) onclick="openGallery(2)" @endif>
                        @if (isset($property->photo_urls[2]))
                            <img src="{{ $property->photo_urls[2] }}" alt="Foto properti: {{ $property->title }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                onerror="this.remove()">
                        @else
                            <div class="h-full w-full bg-gray-200"></div>
                        @endif
                        <div
                            class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-300">
                        </div>
                    </div>
                </div>

                <!-- Gallery Action Buttons -->
                <div class="flex items-center justify-between py-4">
                    <div class="flex items-center gap-3">
                        <span class="text-white/60 text-xs font-semibold flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ count($property->photo_urls) }} foto
                        </span>
                        <span class="text-white/40">•</span>
                        <span class="text-white/60 text-xs font-semibold">Diperbarui
                            {{ $property->updated_at->diffForHumans() }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if (count($property->photo_urls))
                            <button
                                class="flex items-center gap-1.5 bg-white/10 hover:bg-white/20 text-white text-xs font-semibold px-4 py-2 rounded-lg transition"
                                onclick="openGallery(0)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Lihat semua
                            </button>
                        @endif
                        <button
                            class="flex items-center gap-1.5 bg-white/10 hover:bg-white/20 text-white text-xs font-semibold px-4 py-2 rounded-lg transition"
                            id="btnShare">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            Bagikan
                        </button>
                        <button
                            class="flex items-center gap-1.5 bg-white/10 hover:bg-white/20 text-white text-xs font-semibold px-4 py-2 rounded-lg transition"
                            id="btnSave">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
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
                                    <span
                                        class="bg-primary/10 text-primary text-[11px] font-extrabold uppercase tracking-wider px-3 py-1 rounded-full">{{ $property->category === 'rent' ? 'Disewa' : 'Dijual' }}</span>
                                    <span
                                        class="bg-green-50 text-green-600 text-[11px] font-extrabold uppercase tracking-wider px-3 py-1 rounded-full">Terverifikasi</span>
                                </div>
                                <h2 class="text-primary font-extrabold text-3xl md:text-4xl">Rp
                                    {{ number_format((float) $property->price, 0, ',', '.') }}</h2>
                                @if ($property->negotiable)
                                    <p class="text-gray-500 text-sm mt-1">Harga dapat dinegosiasikan</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex items-center gap-1.5 text-gray-400 text-xs font-semibold bg-gray-50 px-3 py-1.5 rounded-lg">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Diperbarui {{ $property->updated_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-extrabold uppercase tracking-[0.15em] text-primary">Info
                                Properti</span>
                        </div>
                        <h3 class="font-outfit text-xl font-extrabold text-secondary mb-4">{{ $property->title }}</h3>
                        <div class="prose-detail text-gray-600 text-sm leading-7" id="descriptionText">
                            <p>{{ $property->description }}</p>
                        </div>
                    </div>

                    <!-- Property Specs Grid -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                        <h3 class="font-outfit text-xl font-extrabold text-secondary mb-6">Informasi lengkap properti
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                            <div
                                class="spec-card flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-4 text-center transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">Tipe</span>
                                <span
                                    class="text-sm font-extrabold text-secondary">{{ ucfirst($property->type) }}</span>
                            </div>
                            <div
                                class="spec-card flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-4 text-center transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                    </svg>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">Luas Tanah</span>
                                <span
                                    class="text-sm font-extrabold text-secondary">{{ number_format((float) $property->land_area_sqm, 0, ',', '.') }}
                                    m²</span>
                            </div>
                            <div
                                class="spec-card flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-4 text-center transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">Luas Bangunan</span>
                                <span
                                    class="text-sm font-extrabold text-secondary">{{ $property->building_area_sqm ? number_format((float) $property->building_area_sqm, 0, ',', '.') . ' m²' : '—' }}</span>
                            </div>
                            <div
                                class="spec-card flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-4 text-center transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">Sertifikat</span>
                                <span
                                    class="text-sm font-extrabold text-secondary">{{ $property->certificate_type }}</span>
                            </div>
                            <div
                                class="spec-card flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-4 text-center transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">Kamar Tidur</span>
                                <span
                                    class="text-sm font-extrabold text-secondary">{{ $property->rooms ?: '—' }}</span>
                            </div>
                            <div
                                class="spec-card flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-4 text-center transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">Kamar Mandi</span>
                                <span
                                    class="text-sm font-extrabold text-secondary">{{ $property->bathrooms ?: '—' }}</span>
                            </div>
                            <div
                                class="spec-card flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-4 text-center transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">Kondisi</span>
                                <span
                                    class="text-sm font-extrabold text-secondary">{{ $property->condition ?: '—' }}</span>
                            </div>
                            <div
                                class="spec-card flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 p-4 text-center transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="text-xs text-gray-400 font-semibold">Akses Jalan</span>
                                <span
                                    class="text-sm font-extrabold text-secondary">{{ $property->road_access ?: '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Additional property information -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                        <h3 class="font-outfit text-xl font-extrabold text-secondary mb-6">Detail tambahan</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach ($propertyFacts as $amenity => $value)
                                <div
                                    class="amenity-item flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 p-3 transition hover:border-primary/30 hover:bg-[#fff5f1]">
                                    <div
                                        class="w-8 h-8 flex-shrink-0 flex items-center justify-center rounded-lg bg-primary/10">
                                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-semibold text-secondary">{{ $amenity }}:
                                        {{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Map Section -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                        <h3 class="font-outfit text-xl font-extrabold text-secondary mb-2">Lokasi Properti</h3>
                        <p class="text-gray-500 text-xs mb-5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-primary" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                            </svg>
                            {{ $property->address }}, {{ $property->district }}, {{ $property->city }},
                            {{ $property->province }}
                        </p>

                        <div class="grid grid-cols-1 lg:grid-cols-[1.2fr_1fr] gap-6">
                            <!-- Map Embed -->
                            <div class="relative rounded-xl overflow-hidden h-[300px] bg-gray-100">
                                <iframe src="{{ $mapUrl }}"
                                    class="absolute inset-0 w-full h-full border-0 rounded-xl" allowfullscreen=""
                                    loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>

                            <!-- Nearby Destinations -->
                            <div>
                                <h4 class="text-sm font-extrabold text-secondary mb-4">Informasi alamat</h4>
                                <div class="space-y-3">
                                    @foreach ($locationFacts as $name => $value)
                                        <div class="flex items-center gap-3 group">
                                            <div
                                                class="w-9 h-9 flex-shrink-0 flex items-center justify-center rounded-lg bg-gray-100 group-hover:bg-primary/10 transition">
                                                <svg class="w-4 h-4 text-gray-400 group-hover:text-primary transition"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-bold text-secondary">{{ $name }}</p>
                                                <p class="text-xs text-gray-400">{{ $value }}</p>
                                            </div>
                                            <span class="text-xs font-bold text-primary">{{ $value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mortgage Calculator -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                        <h3 class="font-outfit text-xl font-extrabold text-secondary mb-1">Hitung cicilan dari rumah
                            impian Anda</h3>
                        <p class="text-gray-500 text-xs mb-6">Simulasi KPR untuk memudahkan perencanaan keuangan Anda
                        </p>

                        <div class="grid grid-cols-1 lg:grid-cols-[1fr_1fr] gap-8">
                            <!-- Calculator Form -->
                            <div class="space-y-5">
                                <div>
                                    <label
                                        class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Harga
                                        Properti</label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">Rp</span>
                                        <input type="text"
                                            value="{{ number_format((float) $property->price, 0, ',', '.') }}"
                                            class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-12 pr-4 text-sm font-semibold text-secondary outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                                            id="mortgagePrice">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Uang
                                            Muka (%)</label>
                                        <div class="relative">
                                            <input type="number" value="20" min="0" max="90"
                                                class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-4 text-sm font-semibold text-secondary outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                                                id="mortgageDown">
                                            <span
                                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">%</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Jangka
                                            Waktu</label>
                                        <div class="relative">
                                            <select
                                                class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-4 text-sm font-semibold text-secondary outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 appearance-none cursor-pointer transition"
                                                id="mortgageTenor">
                                                <option value="10">10 tahun</option>
                                                <option value="15">15 tahun</option>
                                                <option value="20" selected>20 tahun</option>
                                                <option value="25">25 tahun</option>
                                                <option value="30">30 tahun</option>
                                            </select>
                                            <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Suku
                                        Bunga (%)</label>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-400 font-semibold">+ / -</span>
                                        <input type="range" min="3" max="15" value="7.5"
                                            step="0.1" class="mortgage-range flex-1" id="mortgageRate">
                                        <span class="text-sm font-bold text-secondary min-w-[50px] text-right"
                                            id="rateDisplay">7.5%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Calculator Result -->
                            <div class="flex flex-col justify-center">
                                <div class="bg-secondary rounded-2xl p-6 text-center">
                                    <p class="text-white/60 text-xs font-semibold mb-1">Estimasi cicilan Anda</p>
                                    <p class="text-white font-extrabold text-3xl md:text-4xl mb-1"
                                        id="monthlyPayment">—</p>
                                    <p class="text-white/50 text-xs mb-5">per bulan</p>
                                    <button
                                        class="w-full bg-primary hover:bg-[#df3f05] text-white font-bold py-3 px-6 rounded-xl transition text-sm"
                                        onclick="alert('Fitur pengajuan KPR segera hadir!')">
                                        Ajukan KPR Sekarang
                                    </button>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-3 text-center">*Estimasi berdasarkan suku bunga
                                    tetap. Hasil aktual dapat berbeda.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Agent / Advisor -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="font-outfit text-xl font-extrabold text-secondary">Pemilik Properti</h3>
                            <a href="{{ route('user-details', ['uuid' => $property->seller->uuid]) }}"
                                class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:text-[#df3f05] transition">
                                <span>Lihat Profil &amp; Katalog</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                        <a href="{{ route('user-details', ['uuid' => $property->seller->uuid]) }}"
                            class="group flex items-center gap-4 p-2 -mx-2 rounded-xl hover:bg-gray-50 transition">
                            <div class="relative flex-shrink-0">
                                @if ($property->seller->avatar_path)
                                    <img src="{{ $property->seller->avatar_url }}" alt="{{ $property->seller->name }}"
                                        class="w-16 h-16 rounded-full object-cover ring-2 ring-primary/20 ring-offset-2">
                                @else
                                    <div
                                        class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xl font-extrabold ring-2 ring-primary/20 ring-offset-2 group-hover:scale-105 transition-transform">
                                        {{ strtoupper(substr($property->seller->name, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-extrabold text-secondary group-hover:text-primary transition truncate">{{ $property->seller->name }}</h4>
                                <p class="text-xs text-gray-400">Pemilik properti</p>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-[11px] text-primary font-semibold flex items-center gap-1">
                                        Buka halaman profil &amp; katalog penjualan &rarr;
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Right Sidebar (Sticky) -->
                <div class="space-y-5">
                    <div class="sticky top-20 space-y-5">
                        <!-- Agent Card -->
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                            <a href="{{ route('user-details', ['uuid' => $property->seller->uuid]) }}"
                                class="group flex items-center gap-3 mb-4 p-1.5 -mx-1.5 rounded-xl hover:bg-gray-50 transition">
                                <div class="relative flex-shrink-0">
                                    @if ($property->seller->avatar_path)
                                        <img src="{{ $property->seller->avatar_url }}" alt="{{ $property->seller->name }}"
                                            class="w-12 h-12 rounded-full object-cover ring-2 ring-primary/20">
                                    @else
                                        <div
                                            class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center font-extrabold ring-2 ring-primary/20 group-hover:scale-105 transition-transform">
                                            {{ strtoupper(substr($property->seller->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-extrabold text-secondary group-hover:text-primary transition truncate">
                                        {{ $property->seller->name }}
                                    </h4>
                                    <p class="text-xs text-gray-400">Pemilik properti</p>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-primary group-hover:translate-x-0.5 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>

                            <a href="https://wa.me/{{ $property->seller->phone }}"
                                target="_blank" rel="noopener noreferrer"
                                class="flex items-center justify-center gap-2 w-full bg-primary hover:bg-[#df3f05] text-white font-bold py-3 rounded-xl text-sm transition mb-2.5">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                                    <path
                                        d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18a8 8 0 01-4.243-1.212l-.3-.18-3.103.92.92-3.103-.18-.3A8 8 0 1112 20z" />
                                </svg>
                                Hubungi via WhatsApp
                            </a>
                            <a href="tel:+{{ $property->seller->phone }}"
                                class="flex items-center justify-center gap-2 w-full border-2 border-secondary text-secondary hover:bg-secondary hover:text-white font-bold py-3 rounded-xl text-sm transition mb-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                Telepon Pemilik
                            </a>
                            <a href="{{ route('user-details', ['uuid' => $property->seller->uuid]) }}"
                                class="flex items-center justify-center gap-2 w-full bg-gray-50 hover:bg-gray-100 text-secondary hover:text-primary font-bold py-2.5 rounded-xl text-xs transition border border-gray-100">
                                <span>Lihat Profil &amp; Katalog Penjualan</span>
                                <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>

                        <!-- Schedule Visit Card -->
                        <div id="visit" class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm scroll-mt-24">
                            <h4 class="text-sm font-extrabold text-secondary mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Jadwalkan Kunjungan
                            </h4>
                            <p class="text-xs text-gray-400 mb-4">Pilih tanggal dan waktu untuk melihat properti ini secara langsung</p>

                            @if (session('success'))
                                <div class="mb-4 flex items-start gap-2.5 rounded-xl border border-green-200 bg-green-50 p-3 text-xs font-semibold text-green-700">
                                    <svg class="w-4 h-4 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>{{ session('success') }}</span>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="mb-4 flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-semibold text-red-700">
                                    <svg class="w-4 h-4 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ session('error') }}</span>
                                </div>
                            @endif

                            @if ($errors->has('scheduled_at'))
                                <div class="mb-4 flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-semibold text-red-700">
                                    <svg class="w-4 h-4 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ $errors->first('scheduled_at') }}</span>
                                </div>
                            @endif

                            @if ($activeAppointment)
                                @php($isConfirmed = $activeAppointment->status === \App\Models\Appointment::STATUS_CONFIRMED)
                                <div class="rounded-xl border {{ $isConfirmed ? 'border-green-200 bg-green-50' : 'border-amber-200 bg-amber-50' }} p-3">
                                    <div class="flex items-center gap-2 text-xs font-extrabold {{ $isConfirmed ? 'text-green-700' : 'text-amber-700' }}">
                                        @if ($isConfirmed)
                                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                        {{ $isConfirmed ? 'Jadwal Dikonfirmasi' : 'Menunggu Konfirmasi' }}
                                    </div>
                                    <p class="mt-1.5 text-xs font-semibold {{ $isConfirmed ? 'text-green-700' : 'text-amber-700' }}">
                                        {{ $activeAppointment->scheduled_at->locale('id')->translatedFormat('l, d F Y') }}
                                        &middot; {{ $activeAppointment->scheduled_at->format('H:i') }} WIB
                                    </p>
                                </div>

                                <button type="button" disabled
                                    class="mt-3 w-full cursor-not-allowed rounded-xl bg-gray-300 py-3 text-sm font-bold text-gray-600">
                                    {{ $isConfirmed ? 'Jadwal Dikonfirmasi' : 'Menunggu Konfirmasi' }}
                                </button>

                                <a href="{{ route('buyer.survey-schedule') }}"
                                    class="mt-2.5 flex items-center justify-center gap-1.5 text-xs font-bold text-primary transition hover:underline">
                                    Lihat detail jadwal kunjungan
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            @else
                            <form action="{{ route('property.schedule-visit', ['uuid' => $property->uuid]) }}" method="POST" id="scheduleVisitForm" class="space-y-3">
                                @csrf
                                <div>
                                    <label for="visitDateInput" class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Pilih Tanggal Kunjungan</label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-primary pointer-events-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </span>
                                        <input
                                            type="date"
                                            id="visitDateInput"
                                            name="scheduled_at"
                                            min="{{ now()->format('Y-m-d') }}"
                                            value="{{ old('scheduled_at', now()->format('Y-m-d')) }}"
                                            class="visit-calendar-input w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-11 pr-4 text-sm font-semibold text-secondary outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition cursor-pointer"
                                            required
                                        >
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-xs text-gray-500 bg-gray-50 px-3 py-2 rounded-xl border border-gray-100">
                                        <span class="flex items-center gap-1.5 font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary inline-block"></span>
                                            <span id="visitDateFormatted">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                                        </span>
                                        <span id="visitTimeFormatted" class="text-[10px] text-primary bg-primary/10 font-bold px-2 py-0.5 rounded-full border border-primary/20">
                                            {{ str_replace(':', '.', old('scheduled_time', $timeSlots[0])) }} WIB
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Pilih Waktu Kunjungan</label>
                                    <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="Pilih waktu kunjungan">
                                        @foreach ($timeSlots as $slot)
                                            <label class="cursor-pointer">
                                                <input
                                                    type="radio"
                                                    name="scheduled_time"
                                                    value="{{ $slot }}"
                                                    class="peer sr-only"
                                                    @checked(old('scheduled_time', $timeSlots[0]) === $slot)
                                                    required
                                                >
                                                <span class="flex flex-col items-center gap-0.5 rounded-xl border border-gray-200 bg-gray-50 px-2 py-2.5 text-center text-sm font-extrabold text-secondary transition hover:border-primary/40 peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-primary/30">
                                                    {{ str_replace(':', '.', $slot) }}
                                                    <span class="text-[10px] font-semibold uppercase tracking-wider opacity-60">WIB</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @if ($errors->has('scheduled_time'))
                                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $errors->first('scheduled_time') }}</p>
                                    @endif
                                </div>

                                <button type="submit" class="w-full bg-secondary hover:bg-secondary/90 text-white font-bold py-3 rounded-xl text-sm transition shadow-sm hover:shadow active:scale-[0.99]">
                                    Atur Jadwal Kunjungan
                                </button>
                            </form>
                            @endif
                        </div>

                        <!-- Quick Info Card -->
                        <div
                            class="bg-gradient-to-br from-[#fff5f1] to-white rounded-2xl border border-primary/10 p-5">
                            <h4 class="text-sm font-extrabold text-secondary mb-3">Ringkasan Properti</h4>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400 font-semibold">Tipe</span>
                                    <span
                                        class="text-xs font-bold text-secondary">{{ ucfirst($property->type) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400 font-semibold">Lokasi</span>
                                    <span class="text-xs font-bold text-secondary">{{ $property->district }},
                                        {{ $property->city }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400 font-semibold">Luas Tanah</span>
                                    <span
                                        class="text-xs font-bold text-secondary">{{ number_format((float) $property->land_area_sqm, 0, ',', '.') }}
                                        m²</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400 font-semibold">Luas Bangunan</span>
                                    <span
                                        class="text-xs font-bold text-secondary">{{ $property->building_area_sqm ? number_format((float) $property->building_area_sqm, 0, ',', '.') . ' m²' : '—' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400 font-semibold">Kamar Tidur</span>
                                    <span
                                        class="text-xs font-bold text-secondary">{{ $property->rooms ?: '—' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400 font-semibold">Kamar Mandi</span>
                                    <span
                                        class="text-xs font-bold text-secondary">{{ $property->bathrooms ?: '—' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400 font-semibold">Sertifikat</span>
                                    <span
                                        class="text-xs font-bold text-secondary">{{ $property->certificate_type }}</span>
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
                        <h2 class="font-outfit text-3xl font-extrabold text-secondary">Pilihan lain yang mungkin Anda
                            suka!</h2>
                    </div>
                    <a href="{{ route('properties') }}"
                        class="rounded-lg border-2 border-primary px-5 py-2.5 text-sm font-bold text-primary transition hover:bg-primary hover:text-white">Lihat
                        Semua</a>
                </div>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @forelse ($relatedProperties as $relatedProperty)
                        <a href="{{ route('property-details', ['uuid' => $relatedProperty->uuid]) }}"
                            class="similar-card block overflow-hidden rounded-2xl bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                            <div class="relative">
                                @if ($relatedProperty->image_url)
                                    <img src="{{ $relatedProperty->image_url }}" alt="{{ $relatedProperty->title }}"
                                        class="h-48 w-full object-cover">
                                @else
                                    <div class="h-48 w-full bg-gray-200"></div>
                                @endif
                                <span
                                    class="absolute left-3 top-3 rounded-full bg-primary px-3 py-1 text-[11px] font-extrabold uppercase text-white">{{ $relatedProperty->category === 'rent' ? 'Disewa' : 'Dijual' }}</span>
                            </div>
                            <div class="p-4">
                                <p class="mb-1 text-lg font-extrabold text-primary">Rp
                                    {{ number_format((float) $relatedProperty->price, 0, ',', '.') }}</p>
                                <h3 class="mb-2 text-sm font-bold leading-5 text-secondary">
                                    {{ $relatedProperty->title }}</h3>
                                <p class="mb-3 flex items-center gap-1 text-xs text-gray-400">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                                    </svg>
                                    {{ $relatedProperty->district }}, {{ $relatedProperty->city }}
                                </p>
                                <div
                                    class="flex gap-4 border-t border-gray-100 pt-3 text-xs font-semibold text-gray-500">
                                    {{ $relatedProperty->listing_specs }}
                                </div>
                            </div>

                        </a>
                    @empty
                        <p class="col-span-full py-8 text-center text-sm text-gray-500">Belum ada properti serupa yang
                            tersedia.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- CTA Banner -->
        <section class="relative overflow-hidden px-4 py-20 text-center sm:px-6">
            @if (isset($property->photo_urls[0]))
                <img src="{{ $property->photo_urls[0] }}" alt="{{ $property->title }}"
                    class="absolute inset-0 h-full w-full object-cover">
            @else
                <div class="absolute inset-0 bg-secondary"></div>
            @endif
            <div class="absolute inset-0 bg-secondary/85"></div>
            <div class="relative mx-auto max-w-2xl">
                <h2 class="font-outfit text-3xl font-extrabold text-white sm:text-4xl">Siap melihat rumah ini secara
                    langsung?</h2>
                <p class="mx-auto mt-4 max-w-lg leading-7 text-white/70">Hubungi pemilik untuk menjadwalkan kunjungan
                    dan melihat properti ini secara langsung.</p>
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="https://wa.me/{{ $property->seller->phone }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary px-8 py-4 text-base font-extrabold text-white transition hover:bg-[#df3f05]">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                            <path
                                d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z" />
                        </svg>
                        Hubungi WhatsApp
                    </a>
                    <a href="#visit"
                        class="inline-flex items-center gap-2 rounded-xl border-2 border-white/30 px-8 py-4 text-base font-extrabold text-white transition hover:bg-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
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
        <button
            class="absolute top-5 right-5 z-10 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition"
            onclick="closeGallery()">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <div class="absolute top-5 left-5 text-white/60 text-sm font-semibold">
            <span
                id="galleryCounter">{{ count($property->photo_urls) ? '1 / ' . count($property->photo_urls) : '0 / 0' }}</span>
        </div>
        <div class="flex items-center justify-center h-full px-16">
            <button
                class="gallery-nav-btn absolute left-4 z-10 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition"
                onclick="prevImage()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <img id="galleryImage" src="" alt="Gallery"
                class="max-h-[85vh] max-w-[85vw] object-contain rounded-lg">
            <button
                class="gallery-nav-btn absolute right-4 z-10 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition"
                onclick="nextImage()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
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

        function closeMobileNav() {
            mobileNav.classList.remove('open');
            hamburger.classList.remove('open');
            hamburger.setAttribute('aria-expanded', 'false');
        }
        hamburger.addEventListener('click', () => {
            const open = mobileNav.classList.toggle('open');
            hamburger.classList.toggle('open', open);
            hamburger.setAttribute('aria-expanded', String(open));
        });
        document.addEventListener('click', (event) => {
            if (!navbar.contains(event.target) && !mobileNav.contains(event.target)) closeMobileNav();
        });

        // === Gallery ===
        const galleryImages = @json($property->photo_urls);
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
            if (galleryImages.length === 0) {
                return;
            }

            currentImage = (currentImage + 1) % galleryImages.length;
            updateGallery();
        }

        function prevImage() {
            if (galleryImages.length === 0) {
                return;
            }

            currentImage = (currentImage - 1 + galleryImages.length) % galleryImages.length;
            updateGallery();
        }

        function updateGallery() {
            document.getElementById('galleryImage').src = galleryImages[currentImage];
            document.getElementById('galleryCounter').textContent = `${currentImage + 1} / ${galleryImages.length}`;
        }

        document.getElementById('galleryModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('galleryModal') || e.target.closest('.gallery-nav-btn') ===
                null && e.target.tagName !== 'IMG' && e.target.closest('button') === null) {
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

        function updateRateSlider() {
            const min = Number(rateSlider.min);
            const max = Number(rateSlider.max);
            const value = Number(rateSlider.value);
            const thumbSize = parseFloat(getComputedStyle(rateSlider).getPropertyValue('--thumb-size'));
            const trackWidth = rateSlider.clientWidth;
            if (trackWidth <= 0 || !Number.isFinite(thumbSize)) {
                return;
            }

            const ratio = max === min ? 0 : Math.min(1, Math.max(0, (value - min) / (max - min)));
            const thumbCenter = thumbSize / 2 + ratio * (trackWidth - thumbSize);

            rateSlider.style.setProperty('--value', `${(thumbCenter / trackWidth) * 100}%`);
        }

        function calculateMortgage() {
            const priceStr = document.getElementById('mortgagePrice').value.replace(/\./g, '').replace(/,/g, '');
            const price = parseFloat(priceStr) || 0;
            const downPercent = parseFloat(document.getElementById('mortgageDown').value) || 0;
            const tenor = parseInt(document.getElementById('mortgageTenor').value) || 20;
            const rate = parseFloat(rateSlider.value) || 7.5;

            rateDisplay.textContent = rate + '%';
            updateRateSlider();

            const loanAmount = price * (1 - downPercent / 100);
            const monthlyRate = rate / 100 / 12;
            const totalMonths = tenor * 12;

            let monthly;
            if (monthlyRate === 0) {
                monthly = loanAmount / totalMonths;
            } else {
                monthly = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, totalMonths)) / (Math.pow(1 + monthlyRate,
                    totalMonths) - 1);
            }

            const formatted = (monthly / 1000000).toFixed(1);
            monthlyPaymentEl.textContent = `Rp${formatted} juta`;
        }

        rateSlider.addEventListener('input', calculateMortgage);
        window.addEventListener('resize', updateRateSlider);
        document.getElementById('mortgageDown').addEventListener('input', calculateMortgage);
        document.getElementById('mortgageTenor').addEventListener('change', calculateMortgage);
        document.getElementById('mortgagePrice').addEventListener('input', calculateMortgage);
        calculateMortgage();

        // === Visit date calendar handling ===
        const visitDateInput = document.getElementById('visitDateInput');
        const visitDateFormatted = document.getElementById('visitDateFormatted');
        const todayStr = '{{ now()->format("Y-m-d") }}';

        function formatDisplayDate(dateStr) {
            if (!dateStr || !visitDateFormatted) return;
            const parts = dateStr.split('-');
            if (parts.length === 3) {
                const dateObj = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
                const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
                visitDateFormatted.textContent = dateObj.toLocaleDateString('id-ID', options);
            }
        }

        if (visitDateInput) {
            visitDateInput.addEventListener('click', function () {
                if (typeof this.showPicker === 'function') {
                    try {
                        this.showPicker();
                    } catch (err) {}
                }
            });

            visitDateInput.addEventListener('change', function () {
                if (this.value && this.value < todayStr) {
                    this.value = todayStr;
                }
                formatDisplayDate(this.value);
            });

            visitDateInput.addEventListener('input', function () {
                if (this.value && this.value < todayStr) {
                    this.value = todayStr;
                }
                formatDisplayDate(this.value);
            });
        }

        // === Visit time slots ===
        const visitTimeInputs = document.querySelectorAll('input[name="scheduled_time"]');
        const visitTimeFormatted = document.getElementById('visitTimeFormatted');

        visitTimeInputs.forEach((input) => {
            input.addEventListener('change', function () {
                if (!this.checked || !visitTimeFormatted) return;
                visitTimeFormatted.textContent = `${this.value.replace(':', '.')} WIB`;
            });
        });

        // === Fade-in Observer ===
        const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
        }), {
            threshold: 0.12
        });
        document.querySelectorAll('.fade-in').forEach((element) => observer.observe(element));
    </script>
    @include('layouts.partials.chatbot')
</body>

</html>
