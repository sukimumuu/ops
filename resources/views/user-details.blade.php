<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Profil dan katalog properti yang dijual oleh {{ $user->name }} di Mihom">
    <link rel="icon" href="{{ asset('assets/png/favicongray.ico') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" href="{{ asset('assets/png/faviconwhite.ico') }}" media="(prefers-color-scheme: dark)">
    <title>{{ $user->name }} - Profil & Katalog Penjualan | Mihom</title>
    @vite(['resources/css/app.css', 'resources/css/welcome.css', 'resources/js/app.js'])
</head>

<body class="font-manrope bg-[#F8FAFC] text-secondary antialiased min-h-screen flex flex-col justify-between">
    <!-- Navbar Component -->
    <x-navbar />

    <main class="flex-grow pt-16">
        <!-- Breadcrumb & Top Bar -->
        <div class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5">
                <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500">
                    <a href="{{ route('home') }}" class="hover:text-primary transition">Beranda</a>
                    <span>/</span>
                    <a href="{{ route('properties') }}" class="hover:text-primary transition">Properti</a>
                    <span>/</span>
                    <span class="text-secondary font-bold truncate max-w-xs sm:max-w-md">{{ $user->name }}</span>
                </nav>
            </div>
        </div>

        <!-- Banner Header (Pinhome Style) -->
        <div class="relative w-full h-44 sm:h-56 md:h-64 bg-gradient-to-r from-secondary via-[#1e293b] to-secondary overflow-hidden">
            <!-- Decorative wave & subtle background patterns -->
            <div class="absolute inset-0 opacity-20 pointer-events-none">
                <svg class="w-full h-full object-cover" viewBox="0 0 1440 320" fill="none" preserveAspectRatio="none">
                    <path fill="#FC4907" fill-opacity="0.3" d="M0,192L48,197.3C96,203,192,213,288,197.3C384,181,480,139,576,144C672,149,768,203,864,224C960,245,1056,235,1152,208C1248,181,1344,139,1392,117.3L1440,96L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path>
                    <path fill="#ffffff" fill-opacity="0.1" d="M0,64L48,96C96,128,192,192,288,192C384,192,480,128,576,122.7C672,117,768,171,864,192C960,213,1056,203,1152,176C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                </svg>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between relative z-10">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md text-white/90 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Profil Penjual Resmi Mihom
                </span>
            </div>
        </div>

        <!-- Main Profile Container -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 sm:-mt-24 relative z-20 pb-16">
            <!-- Profile Identity Card -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 mb-8">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                    <!-- Left: Avatar & Basic Info -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 sm:gap-6 w-full lg:w-auto">
                        <div class="relative flex-shrink-0">
                            @if ($user->avatar_path)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                    class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover ring-4 ring-white shadow-md">
                            @else
                                <div
                                    class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-primary/10 text-primary flex items-center justify-center text-3xl sm:text-4xl font-extrabold ring-4 ring-white shadow-md">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="absolute bottom-1 right-1 bg-emerald-500 text-white p-1 rounded-full ring-2 ring-white shadow-sm"
                                title="Penjual Terverifikasi">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="space-y-1.5 min-w-0">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <h1 class="font-outfit text-2xl sm:text-3xl font-extrabold text-secondary tracking-tight">
                                    {{ $user->name }}
                                </h1>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Pemilik Properti
                                </span>
                            </div>

                            <p class="text-sm text-gray-500 flex items-center gap-1.5 flex-wrap">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>
                                    {{ collect([$user->district, $user->city, $user->province])->filter()->implode(', ') ?: ($cities->first() ? 'Wilayah ' . $cities->join(', ') : 'Indonesia') }}
                                </span>
                            </p>

                            <div class="flex items-center gap-4 pt-1 text-xs text-gray-400">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Bergabung {{ $user->created_at ? $user->created_at->locale('id')->translatedFormat('F Y') : 'Baru' }}
                                </span>
                                <span>&bull;</span>
                                <span class="font-semibold text-secondary">
                                    {{ $totalProperties }} Listing Aktif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Direct Contact Actions (Pinhome Style) -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto">
                        @if ($user->phone)
                            <a href="https://wa.me/{{ $user->phone }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-primary hover:bg-[#df3f05] text-white font-bold text-sm shadow-md shadow-primary/20 transition-all hover:-translate-y-0.5">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                                    <path
                                        d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18a8 8 0 01-4.243-1.212l-.3-.18-3.103.92.92-3.103-.18-.3A8 8 0 1112 20z" />
                                </svg>
                                <span>Hubungi via WhatsApp</span>
                            </a>
                            <a href="tel:+{{ $user->phone }}"
                                class="inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl border-2 border-secondary text-secondary hover:bg-secondary hover:text-white font-bold text-sm transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span>Telepon</span>
                            </a>
                        @endif
                        <button onclick="navigator.clipboard.writeText(window.location.href); alert('Tautan profil berhasil disalin!');"
                            class="inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 font-bold text-sm transition-colors"
                            title="Bagikan Halaman">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            <span class="sm:hidden lg:inline">Bagikan</span>
                        </button>
                    </div>
                </div>

                <!-- Stats Badges Bar -->
                <div class="mt-8 pt-6 border-t border-gray-100 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center sm:text-left">
                    <div class="bg-gray-50/70 rounded-2xl p-4 border border-gray-100">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Total Properti</p>
                        <p class="font-outfit text-2xl font-extrabold text-secondary">{{ $totalProperties }}</p>
                    </div>
                    <div class="bg-gray-50/70 rounded-2xl p-4 border border-gray-100">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Dijual</p>
                        <p class="font-outfit text-2xl font-extrabold text-primary">{{ $totalSale }}</p>
                    </div>
                    <div class="bg-gray-50/70 rounded-2xl p-4 border border-gray-100">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Disewa</p>
                        <p class="font-outfit text-2xl font-extrabold text-blue-600">{{ $totalRent }}</p>
                    </div>
                    <div class="bg-gray-50/70 rounded-2xl p-4 border border-gray-100">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Cakupan Wilayah</p>
                        <p class="font-outfit text-2xl font-extrabold text-secondary">{{ $cities->count() ?: 1 }} Kota</p>
                    </div>
                </div>
            </div>

            <!-- "Tentang Penjual" Card (Inspired by Reference Image "Tentang Developer") -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 mb-10">
                <h2 class="font-outfit text-xl font-extrabold text-secondary mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Tentang Penjual
                </h2>
                <div class="text-sm text-gray-600 leading-relaxed space-y-3">
                    <p>
                        {{ $user->name }} adalah pemilik properti terverifikasi di platform Mihom. Semua listing yang dipublikasikan oleh akun ini telah melalui verifikasi keaslian awal, didukung dengan data sertifikat yang jelas, dan terbuka untuk negosiasi langsung serta penjadwalan survei ke lokasi.
                    </p>
                    @if ($user->address)
                        <p class="text-gray-500">
                            <strong>Lokasi Operasional / Alamat:</strong> {{ $user->address }}
                        </p>
                    @endif
                </div>

                <!-- Trust Points -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-100">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-secondary">Terverifikasi Resmi</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Identitas pemilik telah terdaftar di sistem Mihom</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-secondary">Respon Langsung</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Dapat dihubungi secara langsung melalui WhatsApp atau telepon</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-secondary">Survei Terjadwal</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Fasilitas pengajuan jadwal survei langsung dari halaman properti</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catalog Section Header (Inspired by "Proyek (2)" in reference) -->
            <div id="katalog" class="scroll-mt-24">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-6 gap-4">
                    <div>
                        <span class="text-xs font-bold text-primary tracking-wider uppercase mb-1 block">Katalog Penjualan</span>
                        <h2 class="font-outfit text-2xl sm:text-3xl font-extrabold text-secondary">
                            Daftar Properti ({{ $properties->total() }})
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Semua properti aktif yang dijual dan disewakan oleh {{ $user->name }}
                        </p>
                    </div>

                    <!-- Sort Dropdown (Pinhome Style: "Terbaru") -->
                    <div class="flex items-center gap-3">
                        <label for="sortSelector" class="text-xs font-semibold text-gray-500 whitespace-nowrap">Urutkan:</label>
                        <form method="GET" action="{{ route('user-details', ['uuid' => $user->uuid]) }}" id="sortForm">
                            @if(request('category'))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif
                            @if(request('type'))
                                <input type="hidden" name="type" value="{{ request('type') }}">
                            @endif
                            <div class="relative">
                                <select name="sort" id="sortSelector" onchange="document.getElementById('sortForm').submit()"
                                    class="appearance-none bg-white border border-gray-200 text-secondary font-bold text-xs sm:text-sm rounded-xl px-4 py-2.5 pr-8 focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer shadow-sm">
                                    <option value="terbaru" {{ $sort === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                                    <option value="termurah" {{ $sort === 'termurah' ? 'selected' : '' }}>Harga Terendah</option>
                                    <option value="termahal" {{ $sort === 'termahal' ? 'selected' : '' }}>Harga Tertinggi</option>
                                </select>
                                <svg class="w-4 h-4 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Filters Pills -->
                <div class="flex flex-wrap items-center gap-2 mb-8">
                    <a href="{{ route('user-details', ['uuid' => $user->uuid, 'sort' => $sort]) }}"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all {{ empty($category) ? 'bg-secondary text-white shadow-sm' : 'bg-white border border-gray-200 text-secondary hover:border-primary hover:text-primary' }}">
                        Semua ({{ $totalProperties }})
                    </a>
                    @if ($totalSale > 0)
                        <a href="{{ route('user-details', ['uuid' => $user->uuid, 'category' => 'sale', 'sort' => $sort]) }}"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all {{ $category === 'sale' ? 'bg-primary text-white shadow-sm shadow-primary/20' : 'bg-white border border-gray-200 text-secondary hover:border-primary hover:text-primary' }}">
                            Dijual ({{ $totalSale }})
                        </a>
                    @endif
                    @if ($totalRent > 0)
                        <a href="{{ route('user-details', ['uuid' => $user->uuid, 'category' => 'rent', 'sort' => $sort]) }}"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all {{ $category === 'rent' ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'bg-white border border-gray-200 text-secondary hover:border-primary hover:text-primary' }}">
                            Disewa ({{ $totalRent }})
                        </a>
                    @endif

                    @foreach ($availableTypes as $t)
                        <a href="{{ route('user-details', ['uuid' => $user->uuid, 'type' => $t, 'category' => $category, 'sort' => $sort]) }}"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold capitalize transition-all {{ $type === $t ? 'bg-secondary text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-primary hover:text-primary' }}">
                            {{ $t }}
                        </a>
                    @endforeach
                </div>

                <!-- Property Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse ($properties as $property)
                        <x-property-card :property="$property" />
                    @empty
                        <div class="col-span-full bg-white rounded-3xl border border-gray-100 p-12 text-center shadow-sm">
                            <div class="w-16 h-16 rounded-full bg-orange-50 text-primary flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h3 class="font-outfit text-lg font-bold text-secondary mb-1">Tidak ada properti ditemukan</h3>
                            <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">
                                Belum ada properti terverifikasi dari penjual ini yang sesuai dengan filter yang Anda pilih.
                            </p>
                            @if (request()->hasAny(['category', 'type', 'sort']))
                                <a href="{{ route('user-details', ['uuid' => $user->uuid]) }}"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-secondary text-white font-bold text-xs hover:bg-secondary/90 transition">
                                    Reset Filter
                                </a>
                            @endif
                        </div>
                    @endforelse
                </div>

                <!-- Pagination Component (Matching Pinhome / Mihom style: < 1 >) -->
                @if ($properties->hasPages())
                    @php
                        $currentPage = $properties->currentPage();
                        $lastPage = $properties->lastPage();
                        $pageNumbers = $lastPage <= 7
                            ? range(1, $lastPage)
                            : array_unique(array_merge([1], range(max(2, $currentPage - 1), min($lastPage - 1, $currentPage + 1)), [$lastPage]));
                        $previousPageNumber = null;
                    @endphp
                    <nav class="flex justify-center items-center gap-2 mt-12" aria-label="Navigasi halaman properti">
                        @if ($properties->onFirstPage())
                            <span aria-disabled="true" class="w-10 h-10 flex justify-center items-center rounded-xl border border-gray-200 text-gray-400 opacity-50 cursor-not-allowed">
                                &larr;
                            </span>
                        @else
                            <a href="{{ $properties->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"
                                class="w-10 h-10 flex justify-center items-center rounded-xl border border-gray-200 text-secondary hover:bg-gray-50 font-bold transition">
                                &larr;
                            </a>
                        @endif

                        @foreach ($pageNumbers as $pageNumber)
                            @if ($previousPageNumber !== null && $pageNumber - $previousPageNumber > 1)
                                <span class="w-8 text-center text-gray-400 font-bold">...</span>
                            @endif

                            @if ($pageNumber === $currentPage)
                                <span aria-current="page" class="w-10 h-10 flex justify-center items-center rounded-xl bg-primary text-white font-extrabold shadow-sm">
                                    {{ $pageNumber }}
                                </span>
                            @else
                                <a href="{{ $properties->url($pageNumber) }}"
                                    class="w-10 h-10 flex justify-center items-center rounded-xl border border-gray-200 text-secondary hover:bg-gray-50 font-bold transition">
                                    {{ $pageNumber }}
                                </a>
                            @endif

                            @php($previousPageNumber = $pageNumber)
                        @endforeach

                        @if ($properties->hasMorePages())
                            <a href="{{ $properties->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"
                                class="w-10 h-10 flex justify-center items-center rounded-xl border border-gray-200 text-secondary hover:bg-gray-50 font-bold transition">
                                &rarr;
                            </a>
                        @else
                            <span aria-disabled="true" class="w-10 h-10 flex justify-center items-center rounded-xl border border-gray-200 text-gray-400 opacity-50 cursor-not-allowed">
                                &rarr;
                            </span>
                        @endif
                    </nav>
                @endif
            </div>

            <!-- Inquire Card -->
            <div class="mt-16 bg-[#FFF7ED] rounded-3xl p-8 sm:p-10 border border-orange-100 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="space-y-2 text-center md:text-left">
                    <span class="text-xs font-bold text-primary tracking-wider uppercase">Tertarik dengan Properti Ini?</span>
                    <h3 class="font-outfit text-2xl font-extrabold text-secondary">
                        Jadwalkan Survei Langsung dengan {{ $user->name }}
                    </h3>
                    <p class="text-sm text-gray-600 max-w-xl">
                        Pilih properti yang Anda minati di atas untuk melihat detail lengkap, foto galeri, spesifikasi, dan mengajukan janji temu survei langsung ke lokasi.
                    </p>
                </div>
                @if ($user->phone)
                    <a href="https://wa.me/{{ $user->phone }}" target="_blank" rel="noopener noreferrer"
                        class="px-8 py-3.5 rounded-xl bg-primary hover:bg-[#df3f05] text-white font-bold text-sm shadow-md shadow-primary/20 transition whitespace-nowrap">
                        Konsultasi via WhatsApp &rarr;
                    </a>
                @endif
            </div>
        </div>
    </main>

    <!-- Footer Component -->
    <x-footer />

    <script>
        const navbar = document.getElementById('navbar');
        const blackLogo = document.getElementById('logo-black');
        const whiteLogo = document.getElementById('logo-white');
        const hamburger = document.getElementById('hamburgerBtn');
        const mobileNav = document.getElementById('mobileNav');

        if (navbar) {
            window.addEventListener('scroll', () => {
                const scrolled = window.scrollY > 40;
                navbar.classList.toggle('scrolled', scrolled);
                if (blackLogo && whiteLogo) {
                    blackLogo.classList.toggle('hidden', !scrolled);
                    blackLogo.classList.toggle('block', scrolled);
                    whiteLogo.classList.toggle('hidden', scrolled);
                }
            });
        }

        if (hamburger && mobileNav) {
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
        }
    </script>
    @include('layouts.partials.chatbot')
</body>

</html>
