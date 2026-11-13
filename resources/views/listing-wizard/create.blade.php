@extends('layouts.app')
@section('page-title', 'Pasang Properti Anda')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        .leaflet-container {
            z-index: 10 !important;
            font-family: inherit;
        }
    </style>
@endpush

@section('content')
    <div class="wizard-page" x-data="listingWizard()" x-cloak
        x-effect="document.body.style.overflow = (showAiModal || showDescriptionConfirm) ? 'hidden' : ''">

        {{-- ===== PAGE HEADER ===== --}}
        <div class="wizard-page-header">
            <a href="{{ route('seller.my-properties') }}" class="wizard-back-link">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
            <h1 class="wizard-page-title">Pasang properti Anda</h1>
            <div class="wizard-draft-badge" x-show="hasDraft">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Draf tersimpan
            </div>
        </div>

        {{-- ===== STEP INDICATOR ===== --}}
        <div class="wizard-steps">
            {{-- Step 1 --}}
            <div class="wizard-step-item" :class="{ 'active': currentStep === 1, 'completed': currentStep > 1 }"
                @click="goToStep(1)">
                <div class="wizard-step-number">
                    <template x-if="currentStep > 1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </template>
                    <template x-if="currentStep <= 1">
                        <span>1</span>
                    </template>
                </div>
                <span class="wizard-step-label">Detail properti</span>
            </div>

            <div class="wizard-step-connector" :class="{ 'active': currentStep > 1 }"></div>

            {{-- Step 2 --}}
            <div class="wizard-step-item" :class="{ 'active': currentStep === 2, 'completed': currentStep > 2 }"
                @click="goToStep(2)">
                <div class="wizard-step-number">
                    <template x-if="currentStep > 2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </template>
                    <template x-if="currentStep <= 2">
                        <span>2</span>
                    </template>
                </div>
                <span class="wizard-step-label">Foto & dokumen</span>
            </div>

            <div class="wizard-step-connector" :class="{ 'active': currentStep > 2 }"></div>

            {{-- Step 3 --}}
            <div class="wizard-step-item" :class="{ 'active': currentStep === 3, 'completed': currentStep > 3 }">
                <div class="wizard-step-number">
                    <span>3</span>
                </div>
                <span class="wizard-step-label">Tinjau & terbitkan</span>
            </div>
        </div>

        {{-- ===== STEP 1: DETAIL PROPERTI ===== --}}
        <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4">

            {{-- ===== AI AUTO-FILL BANNER ===== --}}
            <div class="mb-6 rounded-2xl border border-secondary/20 bg-white shadow-sm">
                <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3l1.7 4.6L18 9.3l-4.3 1.7L12 15.6l-1.7-4.6L6 9.3l4.3-1.7L12 3zM18.5 15l.9 2.4 2.1.9-2.1.9-.9 2.4-.9-2.4-2.1-.9 2.1-.9.9-2.4z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="font-display text-lg font-bold tracking-tight text-secondary sm:text-xl">
                                Isi data iklan dalam hitungan detik!
                            </h2>
                            <p class="mt-1 max-w-xl text-sm leading-relaxed text-slate-500">
                                Tempel informasi properti Anda, lalu biarkan AI mengisi kolom yang relevan. Data yang tidak
                                disebutkan tidak akan diisi.
                            </p>
                        </div>
                    </div>
                    <button x-ref="aiTrigger" type="button" @click="openAiModal()"
                        aria-label="✨ Gunakan AI" aria-haspopup="dialog" aria-controls="ai-auto-fill-modal"
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#e04006] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 active:scale-[.99]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3l1.7 4.6L18 9.3l-4.3 1.7L12 15.6l-1.7-4.6L6 9.3l4.3-1.7L12 3zM18.5 15l.9 2.4 2.1.9-2.1.9-.9 2.4-.9-2.4-2.1-.9 2.1-.9.9-2.4z" />
                        </svg>
                        Gunakan AI
                    </button>
                </div>
            </div>

            <div x-show="aiNotice" x-cloak x-transition role="status" aria-live="polite"
                class="mb-6 flex items-start justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                <p class="leading-relaxed" x-text="aiNotice"></p>
                <button type="button" @click="aiNotice = ''" aria-label="Tutup notifikasi"
                    class="shrink-0 rounded-md p-1 text-emerald-600 transition hover:bg-emerald-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="wizard-card">
                <div class="wizard-card-body">

                    {{-- ===== TIPE PROPERTI SELECTOR ===== --}}
                    <div class="wizard-section">
                        <div class="wizard-section-row">
                            <div class="wizard-section-col">
                                <label class="wz-label">Pilih tipe properti <span class="wz-required">*</span></label>
                            </div>
                            <div class="wizard-section-col text-right">
                                <span class="wz-hint text-sm text-slate-400">* Wajib diisi</span>
                            </div>
                        </div>

                        <div class="wizard-type-grid">
                            {{-- Bangunan Card --}}
                            <label class="wizard-type-card" :class="{ 'selected': form.property_type === 'bangunan' }">
                                <input type="radio" name="property_type" value="bangunan" x-model="form.property_type"
                                    class="sr-only">
                                <div class="wizard-type-check" x-show="form.property_type === 'bangunan'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="wizard-type-icon bangunan">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                                    </svg>
                                </div>
                                <span class="wizard-type-title">Bangunan</span>
                                <span class="wizard-type-desc">Rumah, apartemen, ruko dan lainnya</span>
                            </label>

                            {{-- Tanah Card --}}
                            <label class="wizard-type-card" :class="{ 'selected': form.property_type === 'tanah' }">
                                <input type="radio" name="property_type" value="tanah" x-model="form.property_type"
                                    class="sr-only">
                                <div class="wizard-type-check" x-show="form.property_type === 'tanah'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="wizard-type-icon tanah">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
                                    </svg>
                                </div>
                                <span class="wizard-type-title">Tanah</span>
                                <span class="wizard-type-desc">Kavling, lahan, dan tanah komersial</span>
                            </label>
                        </div>
                        <template x-if="errors.property_type">
                            <p class="wz-error" x-text="errors.property_type"></p>
                        </template>
                    </div>

                    {{-- ===== FORM BANGUNAN ===== --}}
                    <template x-if="form.property_type === 'bangunan'">
                        <div class="wizard-sections-wrapper">
                            {{-- Jenis Bangunan & Tujuan Transaksi --}}
                            <div class="wizard-section">
                                <div class="wizard-form-grid grid-2">
                                    <div class="wizard-field">
                                        <label class="wz-label">Jenis bangunan <span class="wz-required">*</span></label>
                                        <select x-model="form.building_type" class="wz-input">
                                            <option value="">Pilih jenis...</option>
                                            <option value="rumah">Rumah</option>
                                            <option value="apartemen">Apartemen</option>
                                            <option value="ruko">Ruko</option>
                                            <option value="villa">Villa</option>
                                            <option value="gudang">Gudang</option>
                                            <option value="kantor">Kantor</option>
                                        </select>
                                        <template x-if="errors.building_type">
                                            <p class="wz-error" x-text="errors.building_type"></p>
                                        </template>
                                    </div>
                                    <div class="wizard-field">
                                        <label class="wz-label">Tujuan transaksi <span
                                                class="wz-required">*</span></label>
                                        <select x-model="form.transaction_type" class="wz-input">
                                            <option value="">Pilih tujuan...</option>
                                            <option value="dijual">Dijual</option>
                                            <option value="disewakan">Disewakan</option>
                                        </select>
                                        <template x-if="errors.transaction_type">
                                            <p class="wz-error" x-text="errors.transaction_type"></p>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Judul Properti --}}
                            <div class="wizard-section">
                                <div class="wizard-field">
                                    <label class="wz-label">Judul properti <span class="wz-required">*</span></label>
                                    <input type="text" x-model="form.title"
                                        placeholder="Rumah modern 3 lantai di kawasan Bintaro" class="wz-input">
                                    <p class="wz-helper">Gunakan judul yang singkat, jelas, dan menggambarkan properti
                                        Anda.</p>
                                    <template x-if="errors.title">
                                        <p class="wz-error" x-text="errors.title"></p>
                                    </template>
                                </div>
                            </div>

                            {{-- Harga Properti --}}
                            <div class="wizard-section">
                                <div class="wizard-field">
                                    <label class="wz-label">Harga properti <span class="wz-required">*</span></label>
                                    <div class="wz-price-input-group">
                                        <div class="wz-price-input-shell">
                                            <div class="wz-input-group">
                                                <span class="wz-input-prefix">Rp</span>
                                                <input type="text" x-model="form.price" name="price"
                                                    @input="form.price = formatCurrency($event.target.value)"
                                                    placeholder="1.850.000.000" class="wz-input has-prefix">
                                            </div>
                                        </div>
                                        <div class="wz-rent-period-shell" x-show="isRentTransaction()" x-transition>
                                            <select x-model="form.rent_period" name="rent_period"
                                                x-bind:disabled="!isRentTransaction()" class="wz-input"
                                                :class="{ 'opacity-50 cursor-not-allowed': !isRentTransaction() }">
                                                <option value="">Pilih periode</option>
                                                <option value="bulan">Bulan</option>
                                                <option value="tahun">Tahun</option>
                                            </select>
                                        </div>
                                    </div>
                                    <p class="wz-helper">Masukkan harga total properti, bukan harga per meter persegi.</p>
                                    <template x-if="errors.price">
                                        <p class="wz-error" x-text="errors.price"></p>
                                    </template>
                                    <template x-if="errors.rent_period">
                                        <p class="wz-error" x-text="errors.rent_period"></p>
                                    </template>
                                </div>
                                <label class="wz-checkbox-label mt-3">
                                    <input type="checkbox" x-model="form.negotiable" class="wz-checkbox">
                                    <span>Harga dapat dinegosiasikan</span>
                                </label>
                            </div>

                            {{-- Deskripsi Properti --}}
                            <div class="wizard-section">
                                <div class="wizard-field">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <label class="wz-label">Deskripsi properti <span class="wz-required">*</span></label>
                                        @include('listing-wizard.partials.ai-description-button')
                                    </div>
                                    <textarea x-model="form.description" rows="5"
                                        placeholder="Rumah modern siap huni dengan pencahayaan alam, ruang keluarga yang luas, dan taman pribadi. Berada di lingkungan yang tenang dengan keamanan 24 jam. Dekat sekolah, pusat perbelanjaan, dan akses tol bintaro."
                                        class="wz-input wz-textarea"></textarea>
                                    <p class="wz-helper">Cantumkan kondisi, keunggulan, dan akses di sekitar properti.</p>
                                    <div x-show="descriptionNotice" x-cloak role="status" aria-live="polite"
                                        class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium leading-relaxed text-emerald-700"
                                        x-text="descriptionNotice"></div>
                                    <div x-show="descriptionError" x-cloak role="alert"
                                        class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium leading-relaxed text-red-600"
                                        x-text="descriptionError"></div>
                                    <template x-if="errors.description">
                                        <p class="wz-error" x-text="errors.description"></p>
                                    </template>
                                </div>
                            </div>

                            {{-- ===== LOKASI PROPERTI ===== --}}
                            <div class="wizard-divider"></div>
                            <div class="wizard-section">
                                <h3 class="wizard-section-title">Lokasi properti</h3>
                                <p class="wizard-section-subtitle">Alamat yang akurat membantu calon pembeli menemukan
                                    properti Anda.</p>

                                @include('listing-wizard.partials.location-fields')

                                <div class="wizard-field mt-4">
                                    <label class="wz-label">Alamat lengkap <span class="wz-required">*</span></label>
                                    <input type="text" x-model="form.address"
                                        placeholder="Jl. Bintaro Utama, Sektor 9, Blok H No. 12" class="wz-input">
                                    <div class="wz-helper-icon mt-2">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Nomor rumah tidak ditampilkan ke publik. Lokasi akan ditampilkan secara
                                            perkiraan.</span>
                                    </div>
                                    <template x-if="errors.address">
                                        <p class="wz-error" x-text="errors.address"></p>
                                    </template>
                                </div>

                                {{-- Map --}}
                                <div class="wizard-field mt-4">
                                    <label class="wz-label">Titik lokasi di peta</label>
                                    <div class="wz-map-search">
                                        <input type="text" x-model="searchQuery"
                                            @keydown.enter.prevent="searchLocation()"
                                            placeholder="Cari alamat, nama jalan, atau daerah..."
                                            class="wz-input flex-1 text-sm">
                                        <button type="button" @click="searchLocation()" :disabled="isSearching"
                                            class="wz-btn-search">
                                            <span x-show="!isSearching">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                            </span>
                                            <span x-show="isSearching">
                                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4" />
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                                </svg>
                                            </span>
                                        </button>
                                    </div>
                                    <template x-if="searchError">
                                        <p class="wz-error mt-2" x-text="searchError"></p>
                                    </template>

                                    <div class="wz-map-container mt-3">
                                        <div id="wizard-map" class="wz-map" x-init="initMap()"></div>
                                    </div>

                                    <div class="wizard-form-grid grid-2 mt-3">
                                        <div class="wizard-field">
                                            <label class="wz-label-sm">Latitude</label>
                                            <input type="number" step="0.00000001" x-model="form.latitude"
                                                placeholder="-6.12345678" class="wz-input text-sm">
                                        </div>
                                        <div class="wizard-field">
                                            <label class="wz-label-sm">Longitude</label>
                                            <input type="number" step="0.00000001" x-model="form.longitude"
                                                placeholder="106.12345678" class="wz-input text-sm">
                                        </div>
                                    </div>
                                    <input type="hidden" id="osm_url" x-model="osmUrl">
                                </div>
                            </div>

                            {{-- ===== SPESIFIKASI BANGUNAN ===== --}}
                            <div class="wizard-divider"></div>
                            <div class="wizard-section">
                                <h3 class="wizard-section-title">Spesifikasi bangunan</h3>
                                <p class="wizard-section-subtitle">Lengkapi ukuran, kondisi, dan legalitas properti.</p>

                                <div class="wizard-form-grid grid-2 mt-5">
                                    <div class="wizard-field">
                                        <label class="wz-label">Luas tanah <span class="wz-required">*</span></label>
                                        <div class="wz-input-group">
                                            <input type="number" x-model="form.land_area_sqm" placeholder="120"
                                                min="0" class="wz-input has-suffix">
                                            <span class="wz-input-suffix">m²</span>
                                        </div>
                                        <template x-if="errors.land_area_sqm">
                                            <p class="wz-error" x-text="errors.land_area_sqm"></p>
                                        </template>
                                    </div>
                                    <div class="wizard-field">
                                        <label class="wz-label">Luas bangunan <span class="wz-required">*</span></label>
                                        <div class="wz-input-group">
                                            <input type="number" x-model="form.building_area_sqm" placeholder="150"
                                                min="0" class="wz-input has-suffix">
                                            <span class="wz-input-suffix">m²</span>
                                        </div>
                                        <template x-if="errors.building_area_sqm">
                                            <p class="wz-error" x-text="errors.building_area_sqm"></p>
                                        </template>
                                    </div>
                                </div>

                                <div class="wizard-form-grid grid-3 mt-4">
                                    <div class="wizard-field">
                                        <label class="wz-label">Kamar tidur <span class="wz-required">*</span></label>
                                        <input type="number" x-model="form.bedrooms" placeholder="3" min="0"
                                            class="wz-input">
                                        <template x-if="errors.bedrooms">
                                            <p class="wz-error" x-text="errors.bedrooms"></p>
                                        </template>
                                    </div>
                                    <div class="wizard-field">
                                        <label class="wz-label">Kamar mandi <span class="wz-required">*</span></label>
                                        <input type="number" x-model="form.bathrooms" placeholder="2" min="0"
                                            class="wz-input">
                                        <template x-if="errors.bathrooms">
                                            <p class="wz-error" x-text="errors.bathrooms"></p>
                                        </template>
                                    </div>
                                    <div class="wizard-field">
                                        <label class="wz-label">Jumlah lantai <span class="wz-required">*</span></label>
                                        <input type="number" x-model="form.floors" placeholder="2" min="1"
                                            class="wz-input">
                                        <template x-if="errors.floors">
                                            <p class="wz-error" x-text="errors.floors"></p>
                                        </template>
                                    </div>
                                </div>

                                <div class="wizard-form-grid mt-4">
                                    <div class="wizard-field">
                                        <label class="wz-label">Kondisi bangunan <span
                                                class="wz-required">*</span></label>
                                        <select x-model="form.condition" class="wz-input">
                                            <option value="">Pilih kondisi...</option>
                                            <option value="baru">Baru</option>
                                            <option value="siap_huni">Siap huni</option>
                                            <option value="butuh_renovasi">Butuh renovasi</option>
                                        </select>
                                        <template x-if="errors.condition">
                                            <p class="wz-error" x-text="errors.condition"></p>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- ===== FORM TANAH ===== --}}
                    <template x-if="form.property_type === 'tanah'">
                        <div class="wizard-sections-wrapper">
                            {{-- Jenis Tanah & Tujuan Transaksi --}}
                            <div class="wizard-section">
                                <div class="wizard-form-grid grid-2">
                                    <div class="wizard-field">
                                        <label class="wz-label">Jenis tanah <span class="wz-required">*</span></label>
                                        <select x-model="form.land_type" class="wz-input">
                                            <option value="">Pilih jenis...</option>
                                            <option value="kavling">Kavling</option>
                                            <option value="sawah">Sawah</option>
                                            <option value="kebun">Kebun</option>
                                            <option value="komersial">Komersial</option>
                                            <option value="industri">Industri</option>
                                        </select>
                                        <template x-if="errors.land_type">
                                            <p class="wz-error" x-text="errors.land_type"></p>
                                        </template>
                                    </div>
                                    <div class="wizard-field">
                                        <label class="wz-label">Tujuan transaksi <span
                                                class="wz-required">*</span></label>
                                        <select x-model="form.transaction_type" class="wz-input">
                                            <option value="">Pilih tujuan...</option>
                                            <option value="dijual">Dijual</option>
                                            <option value="disewakan">Disewakan</option>
                                        </select>
                                        <template x-if="errors.transaction_type">
                                            <p class="wz-error" x-text="errors.transaction_type"></p>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Judul Properti --}}
                            <div class="wizard-section">
                                <div class="wizard-field">
                                    <label class="wz-label">Judul properti <span class="wz-required">*</span></label>
                                    <input type="text" x-model="form.title"
                                        placeholder="Tanah kavling strategis di pinggir jalan utama" class="wz-input">
                                    <p class="wz-helper">Gunakan judul yang singkat, jelas, dan menggambarkan properti
                                        Anda.</p>
                                    <template x-if="errors.title">
                                        <p class="wz-error" x-text="errors.title"></p>
                                    </template>
                                </div>
                            </div>

                            {{-- Harga Properti --}}
                            <div class="wizard-section">
                                <div class="wizard-field">
                                    <label class="wz-label">Harga properti <span class="wz-required">*</span></label>
                                    <div class="wz-price-input-group">
                                        <div class="wz-price-input-shell">
                                            <div class="wz-input-group">
                                                <span class="wz-input-prefix">Rp</span>
                                                <input type="text" x-model="form.price" name="price"
                                                    @input="form.price = formatCurrency($event.target.value)"
                                                    placeholder="750.000.000" class="wz-input has-prefix">
                                            </div>
                                        </div>
                                        <div class="wz-rent-period-shell" x-show="isRentTransaction()" x-transition>
                                            <select x-model="form.rent_period" name="rent_period"
                                                x-bind:disabled="!isRentTransaction()" class="wz-input"
                                                :class="{ 'opacity-50 cursor-not-allowed': !isRentTransaction() }">
                                                <option value="">Pilih periode</option>
                                                <option value="bulan">Bulan</option>
                                                <option value="tahun">Tahun</option>
                                            </select>
                                        </div>
                                    </div>
                                    <p class="wz-helper">Masukkan harga total tanah, bukan harga per meter persegi.</p>
                                    <template x-if="errors.price">
                                        <p class="wz-error" x-text="errors.price"></p>
                                    </template>
                                    <template x-if="errors.rent_period">
                                        <p class="wz-error" x-text="errors.rent_period"></p>
                                    </template>
                                </div>
                                <label class="wz-checkbox-label mt-3">
                                    <input type="checkbox" x-model="form.negotiable" class="wz-checkbox">
                                    <span>Harga dapat dinegosiasikan</span>
                                </label>
                            </div>

                            {{-- Deskripsi Properti --}}
                            <div class="wizard-section">
                                <div class="wizard-field">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <label class="wz-label">Deskripsi properti <span class="wz-required">*</span></label>
                                        @include('listing-wizard.partials.ai-description-button')
                                    </div>
                                    <textarea x-model="form.description" rows="5"
                                        placeholder="Tanah kavling siap bangun dengan akses jalan lebar. Cocok untuk hunian atau investasi. Lokasi strategis dekat dengan fasilitas publik dan akses tol."
                                        class="wz-input wz-textarea"></textarea>
                                    <p class="wz-helper">Cantumkan kondisi tanah, topografi, akses jalan, dan potensi
                                        pengembangan.</p>
                                    <div x-show="descriptionNotice" x-cloak role="status" aria-live="polite"
                                        class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium leading-relaxed text-emerald-700"
                                        x-text="descriptionNotice"></div>
                                    <div x-show="descriptionError" x-cloak role="alert"
                                        class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium leading-relaxed text-red-600"
                                        x-text="descriptionError"></div>
                                    <template x-if="errors.description">
                                        <p class="wz-error" x-text="errors.description"></p>
                                    </template>
                                </div>
                            </div>

                            {{-- ===== LOKASI PROPERTI (Tanah) ===== --}}
                            <div class="wizard-divider"></div>
                            <div class="wizard-section">
                                <h3 class="wizard-section-title">Lokasi properti</h3>
                                <p class="wizard-section-subtitle">Alamat yang akurat membantu calon pembeli menemukan
                                    properti Anda.</p>

                                @include('listing-wizard.partials.location-fields')

                                <div class="wizard-field mt-4">
                                    <label class="wz-label">Alamat lengkap <span class="wz-required">*</span></label>
                                    <input type="text" x-model="form.address"
                                        placeholder="Jl. Raya Serpong, Kavling Blok A No. 5" class="wz-input">
                                    <div class="wz-helper-icon mt-2">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Nomor rumah tidak ditampilkan ke publik. Lokasi akan ditampilkan secara
                                            perkiraan.</span>
                                    </div>
                                    <template x-if="errors.address">
                                        <p class="wz-error" x-text="errors.address"></p>
                                    </template>
                                </div>

                                {{-- Map --}}
                                <div class="wizard-field mt-4">
                                    <label class="wz-label">Titik lokasi di peta</label>
                                    <div class="wz-map-search">
                                        <input type="text" x-model="searchQuery"
                                            @keydown.enter.prevent="searchLocation()"
                                            placeholder="Cari alamat, nama jalan, atau daerah..."
                                            class="wz-input flex-1 text-sm">
                                        <button type="button" @click="searchLocation()" :disabled="isSearching"
                                            class="wz-btn-search">
                                            <span x-show="!isSearching">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                            </span>
                                            <span x-show="isSearching">
                                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4" />
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                                </svg>
                                            </span>
                                        </button>
                                    </div>
                                    <template x-if="searchError">
                                        <p class="wz-error mt-2" x-text="searchError"></p>
                                    </template>

                                    <div class="wz-map-container mt-3">
                                        <div id="wizard-map-tanah" class="wz-map" x-init="initMap('wizard-map-tanah')"></div>
                                    </div>

                                    <div class="wizard-form-grid grid-2 mt-3">
                                        <div class="wizard-field">
                                            <label class="wz-label-sm">Latitude</label>
                                            <input type="number" step="0.00000001" x-model="form.latitude"
                                                placeholder="-6.12345678" class="wz-input text-sm">
                                        </div>
                                        <div class="wizard-field">
                                            <label class="wz-label-sm">Longitude</label>
                                            <input type="number" step="0.00000001" x-model="form.longitude"
                                                placeholder="106.12345678" class="wz-input text-sm">
                                        </div>
                                    </div>
                                    <input type="hidden" id="osm_url" x-model="osmUrl">
                                </div>
                            </div>

                            {{-- ===== SPESIFIKASI TANAH ===== --}}
                            <div class="wizard-divider"></div>
                            <div class="wizard-section">
                                <h3 class="wizard-section-title">Spesifikasi tanah</h3>
                                <p class="wizard-section-subtitle">Lengkapi informasi luas, topografi, dan legalitas tanah.
                                </p>

                                <div class="wizard-form-grid grid-2 mt-5">
                                    <div class="wizard-field">
                                        <label class="wz-label">Luas tanah <span class="wz-required">*</span></label>
                                        <div class="wz-input-group">
                                            <input type="number" x-model="form.land_area_sqm" placeholder="500"
                                                min="0" class="wz-input has-suffix">
                                            <span class="wz-input-suffix">m²</span>
                                        </div>
                                        <template x-if="errors.land_area_sqm">
                                            <p class="wz-error" x-text="errors.land_area_sqm"></p>
                                        </template>
                                    </div>
                                    <div class="wizard-field">
                                        <label class="wz-label">Lebar depan</label>
                                        <div class="wz-input-group">
                                            <input type="number" x-model="form.front_width" placeholder="20"
                                                min="0" class="wz-input has-suffix">
                                            <span class="wz-input-suffix">m</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="wizard-form-grid grid-2 mt-4">
                                    <div class="wizard-field">
                                        <label class="wz-label">Kontur tanah <span class="wz-required">*</span></label>
                                        <select x-model="form.land_contour" class="wz-input">
                                            <option value="">Pilih kontur...</option>
                                            <option value="datar">Datar</option>
                                            <option value="miring">Miring</option>
                                            <option value="berbukit">Berbukit</option>
                                        </select>
                                        <template x-if="errors.land_contour">
                                            <p class="wz-error" x-text="errors.land_contour"></p>
                                        </template>
                                    </div>
                                    <div class="wizard-field">
                                        <label class="wz-label">Akses jalan <span class="wz-required">*</span></label>
                                        <select x-model="form.road_access" class="wz-input">
                                            <option value="">Pilih akses...</option>
                                            <option value="jalan_utama">Jalan utama</option>
                                            <option value="jalan_lingkungan">Jalan lingkungan</option>
                                            <option value="gang">Gang</option>
                                        </select>
                                        <template x-if="errors.road_access">
                                            <p class="wz-error" x-text="errors.road_access"></p>
                                        </template>
                                    </div>
                                </div>

                                <div class="wizard-form-grid mt-4">
                                    <div class="wizard-field">
                                        <label class="wz-label">Peruntukan zona</label>
                                        <select x-model="form.zone_type" class="wz-input">
                                            <option value="">Pilih zona...</option>
                                            <option value="perumahan">Perumahan</option>
                                            <option value="komersial">Komersial</option>
                                            <option value="industri">Industri</option>
                                            <option value="pertanian">Pertanian</option>
                                            <option value="campuran">Campuran</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                </div>

                {{-- Card Footer --}}
                <div class="wizard-card-footer" x-show="form.property_type">
                    <button type="button" class="wz-btn-draft" @click="saveDraft()">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Simpan draf
                    </button>
                    <button type="button" @click="submitStepOne()" :disabled="isLoading" class="wz-btn-primary">
                        <span x-show="!isLoading">Lanjut ke foto & dokumen</span>
                        <span x-show="isLoading" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            Menyimpan...
                        </span>
                        <svg x-show="!isLoading" class="w-4 h-4" fill="none" stroke="currentColor"
                            stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== STEP 2: FOTO & DOKUMEN ===== --}}
        <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4">

            <div class="wizard-card">
                <div class="wizard-card-body">

                    {{-- Foto Properti --}}
                    <div class="wizard-section">
                        <h3 class="wizard-section-title">Foto properti</h3>
                        <p class="wizard-section-subtitle">Upload foto-foto terbaik properti Anda. Minimal 1 foto, maksimal
                            10 foto.</p>

                        <div class="wz-photo-grid mt-5" id="photo-preview-grid">
                            <template x-for="(photo, index) in photoPreviews" :key="index">
                                <div class="wz-photo-item">
                                    <img :src="photo" class="wz-photo-img" alt="Preview">
                                    <div class="wz-photo-overlay">
                                        <button @click="removePhoto(index)" class="wz-photo-remove">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <template x-if="index === 0">
                                        <span class="wz-photo-badge">Utama</span>
                                    </template>
                                </div>
                            </template>

                            <template x-if="photoPreviews.length < 10">
                                <label class="wz-photo-add">
                                    <div class="wz-photo-add-icon">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </div>
                                    <span>Tambah Foto</span>
                                    <input type="file" accept="image/jpeg,image/png,image/webp" multiple
                                        class="hidden" @change="handlePhotoUpload($event)">
                                </label>
                            </template>
                        </div>
                        <template x-if="errors.photos">
                            <p class="wz-error mt-2" x-text="errors.photos"></p>
                        </template>
                    </div>

                    {{-- Sertifikat & Dokumen --}}
                    <div class="wizard-divider"></div>
                    <div class="wizard-section">
                        <h3 class="wizard-section-title">Sertifikat & dokumen legal</h3>
                        <p class="wizard-section-subtitle">Unggah sertifikat properti beserta dokumen pendukung. File
                            diamankan menggunakan Signed URL.</p>

                        {{-- Main certificate entries --}}
                        <div class="wz-cert-list mt-4">
                            <template x-for="(certificate, index) in certificates" :key="certificate.key">
                                <div class="wz-cert-card" data-certificate-entry>
                                    <div class="wz-cert-form">
                                        <div class="wz-cert-col">
                                            <label class="wz-label" :for="'jenis-sertifikat-' + index">Jenis surat <span
                                                    class="wz-required">*</span></label>
                                            <select :id="'jenis-sertifikat-' + index"
                                                x-model="certificate.jenis_sertifikat"
                                                :name="certificateFieldName(index, 'jenis_sertifikat')" class="wz-input">
                                                <option value="">Pilih jenis surat</option>
                                                <option value="SHM">SHM — Sertifikat Hak Milik</option>
                                                <option value="SHGB">SHGB — Hak Guna Bangunan</option>
                                                <option value="GIRIK">Girik</option>
                                                <option value="AJB">AJB — Akta Jual Beli</option>
                                                <option value="OTHER">Lainnya</option>
                                            </select>
                                            <template x-if="certificateError(index, 'jenis_sertifikat')">
                                                <p class="wz-error" x-text="certificateError(index, 'jenis_sertifikat')">
                                                </p>
                                            </template>
                                        </div>

                                        <div class="wz-cert-col">
                                            <span class="wz-label">Upload file <span class="wz-required">*</span></span>
                                            <div class="wz-dropzone wz-cert-dropzone"
                                                :class="{
                                                    'is-dragging': dragging === 'certificate-' + index,
                                                    'has-file': !!certificate.file
                                                }"
                                                @dragenter.prevent="dragging = 'certificate-' + index"
                                                @dragover.prevent="dragging = 'certificate-' + index"
                                                @dragleave.prevent="dragging = ''"
                                                @drop.prevent="handleCertificateDrop($event, index)">
                                                <label class="wz-dropzone-label" :for="'certificate-input-' + index">
                                                    <span class="wz-dropzone-icon" x-show="!certificate.file"
                                                        aria-hidden="true">
                                                        <x-cloud-upload-icon class="h-10 w-10" />
                                                    </span>
                                                    <span class="wz-dropzone-text">
                                                        <span class="wz-dropzone-title" x-show="!certificate.file">Pilih
                                                            file</span>
                                                        <span class="wz-dropzone-desc" x-show="!certificate.file">atau
                                                            drag &amp; drop</span>
                                                        <span class="wz-dropzone-filename" x-show="certificate.file"
                                                            x-text="certificate.file?.name || ''"
                                                            :title="certificate.file?.name || ''" aria-live="polite"></span>
                                                        <span class="wz-dropzone-desc" x-show="certificate.file">
                                                            File siap diunggah · <span
                                                                x-text="fileMeta(certificate.file)"></span>
                                                        </span>
                                                    </span>
                                                    <input type="file" :id="'certificate-input-' + index"
                                                        :name="certificateFieldName(index, 'certificate_document')"
                                                        accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                                        class="sr-only" :disabled="!!certificate.file"
                                                        @change="handleCertificateUpload($event, index)">
                                                </label>
                                                <button type="button" class="wz-dropzone-remove"
                                                    x-show="certificate.file"
                                                    @click.stop.prevent="removeCertificateFile(index, $event)"
                                                    :aria-label="'Remove selected file ' + (certificate.file?.name || '')">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                        stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <p class="wz-file-hint">PDF, JPG, atau PNG · Maks. 10MB</p>
                                            <template x-if="certificateError(index, 'certificate_document')">
                                                <p class="wz-error" x-text="certificateError(index, 'certificate_document')">
                                                </p>
                                            </template>
                                        </div>

                                        <button type="button" x-show="index > 0"
                                            @click="removeCertificateEntry(index)" class="wz-cert-cancel">
                                            Batalkan sertifikat tambahan
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <button type="button" class="wz-cert-add" @click="addCertificateEntry()"
                                :disabled="certificates.length >= maxCertificates"
                                :aria-disabled="certificates.length >= maxCertificates">
                                <span class="wz-cert-add-left">
                                    <span class="wz-cert-add-icon" aria-hidden="true">
                                        <x-cloud-upload-icon class="h-8 w-8" />
                                    </span>
                                    <span class="wz-cert-add-text">
                                        <span class="wz-cert-add-title">Tambah Sertifikat</span>
                                        <span class="wz-cert-add-desc">Klik untuk upload file</span>
                                    </span>
                                </span>
                                <span class="wz-cert-add-plus" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                </span>
                            </button>
                        </div>

                        {{-- Optional supporting documents --}}
                        <div class="wz-doc-list">
                            @include('listing-wizard.partials.document-upload', [
                                'key' => 'imb',
                                'inputId' => 'imb-document-input',
                                'name' => 'imb_document',
                                'label' => 'Upload IMB / PBG (Optional)',
                            ])
                            @include('listing-wizard.partials.document-upload', [
                                'key' => 'pbb',
                                'inputId' => 'pbb-document-input',
                                'name' => 'pbb_document',
                                'label' => 'Upload SPPT dan STTS PBB (Optional)',
                            ])
                        </div>

                        {{-- Security Info --}}
                        <div class="wz-info-box mt-4">
                            <div class="wz-info-icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <div>
                                <p class="wz-info-title">File sertifikat diamankan</p>
                                <p class="wz-info-desc">Akses ke file sertifikat menggunakan <strong>Signed Temporary
                                        URL</strong> dari Laravel yang berlaku selama 10 menit untuk keamanan dokumen
                                    sensitif.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Agreement --}}
                    <div class="wizard-section">
                        <label class="wz-checkbox-label wz-agreement">
                            <input type="checkbox" x-model="agreedTerms" class="wz-checkbox">
                            <span>Saya menyatakan bahwa data yang saya masukkan adalah <strong>benar dan valid</strong>.
                                Foto dan sertifikat yang diunggah sesuai dengan properti yang didaftarkan.</span>
                        </label>
                    </div>
                </div>

                {{-- Card Footer --}}
                <div class="wizard-card-footer">
                    <button type="button" @click="currentStep = 1" class="wz-btn-back">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                        </svg>
                        Kembali
                    </button>
                    <button type="button" @click="submitStepTwo()" :disabled="isLoading || !agreedTerms"
                        class="wz-btn-primary">
                        <span x-show="!isLoading">Lanjutkan</span>
                        <span x-show="isLoading" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            Mengunggah...
                        </span>
                        <svg x-show="!isLoading" class="w-4 h-4" fill="none" stroke="currentColor"
                            stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== STEP 3: REVIEW & SUBMIT ===== --}}
        <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4">

            <div class="wizard-card">
                <div class="wizard-card-body">

                    {{-- Review: Detail Properti --}}
                    <div class="wz-review-block">
                        <div class="wz-review-header">
                            <h4>Detail properti</h4>
                            <button @click="currentStep = 1" class="wz-review-edit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                Edit
                            </button>
                        </div>
                        <div class="wz-review-body">
                            <div class="wz-review-grid">
                                <div class="wz-review-item">
                                    <span class="wz-review-label">Tipe properti</span>
                                    <span class="wz-review-value"
                                        x-text="form.property_type === 'bangunan' ? 'Bangunan' : 'Tanah'"></span>
                                </div>
                                <div class="wz-review-item">
                                    <span class="wz-review-label">Judul</span>
                                    <span class="wz-review-value" x-text="form.title || '-'"></span>
                                </div>
                                <div class="wz-review-item">
                                    <span class="wz-review-label">Harga</span>
                                    <span class="wz-review-value wz-price" x-text="'Rp ' + (form.price || '0')"></span>
                                </div>
                                <div class="wz-review-item">
                                    <span class="wz-review-label">Sertifikat</span>
                                    <span class="wz-review-value" x-text="certificateTypeLabel()"></span>
                                </div>
                                <div class="wz-review-item">
                                    <span class="wz-review-label">Luas tanah</span>
                                    <span class="wz-review-value" x-text="(form.land_area_sqm || '0') + ' m²'"></span>
                                </div>
                                <template x-if="form.property_type === 'bangunan'">
                                    <div class="wz-review-item">
                                        <span class="wz-review-label">Luas bangunan</span>
                                        <span class="wz-review-value"
                                            x-text="form.building_area_sqm ? form.building_area_sqm + ' m²' : '-'"></span>
                                    </div>
                                </template>
                                <template x-if="form.property_type === 'bangunan'">
                                    <div class="wz-review-item">
                                        <span class="wz-review-label">Kamar tidur / mandi</span>
                                        <span class="wz-review-value"
                                            x-text="(form.bedrooms || '0') + ' KT / ' + (form.bathrooms || '0') + ' KM'"></span>
                                    </div>
                                </template>
                                <template x-if="form.property_type === 'tanah'">
                                    <div class="wz-review-item">
                                        <span class="wz-review-label">Kontur tanah</span>
                                        <span class="wz-review-value" x-text="form.land_contour || '-'"></span>
                                    </div>
                                </template>
                            </div>
                            <div class="wz-review-item mt-3" style="grid-column: 1/-1;">
                                <span class="wz-review-label">Deskripsi</span>
                                <p class="wz-review-desc" x-text="form.description || '-'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Review: Lokasi --}}
                    <div class="wz-review-block">
                        <div class="wz-review-header">
                            <h4>Lokasi</h4>
                            <button @click="currentStep = 1" class="wz-review-edit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                Edit
                            </button>
                        </div>
                        <div class="wz-review-body">
                            <div class="wz-review-grid">
                                <div class="wz-review-item" style="grid-column: 1/-1;">
                                    <span class="wz-review-label">Alamat</span>
                                    <span class="wz-review-value" x-text="form.address || '-'"></span>
                                </div>
                                <div class="wz-review-item">
                                    <span class="wz-review-label">Provinsi</span>
                                    <span class="wz-review-value" x-text="form.province || '-'"></span>
                                </div>
                                <div class="wz-review-item">
                                    <span class="wz-review-label">Kota/Kabupaten</span>
                                    <span class="wz-review-value" x-text="form.city || '-'"></span>
                                </div>
                                <div class="wz-review-item">
                                    <span class="wz-review-label">Kecamatan</span>
                                    <span class="wz-review-value" x-text="form.district || '-'"></span>
                                </div>
                                <div class="wz-review-item">
                                    <span class="wz-review-label">Koordinat</span>
                                    <span class="wz-review-value"
                                        x-text="(form.latitude && form.longitude) ? form.latitude + ', ' + form.longitude : 'Belum ditentukan'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Review: Foto & Dokumen --}}
                    <div class="wz-review-block">
                        <div class="wz-review-header">
                            <h4>Foto & dokumen</h4>
                            <button @click="currentStep = 2" class="wz-review-edit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                Edit
                            </button>
                        </div>
                        <div class="wz-review-body">
                            <div class="wz-review-item">
                                <span class="wz-review-label">Foto properti</span>
                                <div class="wz-review-photos">
                                    <template x-for="(photo, index) in photoPreviews" :key="'review-' + index">
                                        <div class="wz-review-photo-thumb">
                                            <img :src="photo" alt="Preview">
                                        </div>
                                    </template>
                                </div>
                                <p class="wz-review-photo-count" x-text="photoPreviews.length + ' foto diunggah'"></p>
                            </div>
                            <div class="wz-review-item mt-3">
                                <span class="wz-review-label">Sertifikat</span>
                                <template x-for="(certificate, index) in certificates" :key="'review-cert-' + certificate.key">
                                    <div class="wz-review-cert-badge" x-show="certificate.file">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span x-text="certificate.file?.name"></span>
                                    </div>
                                </template>
                                <template x-if="!hasCertificateFile()">
                                    <span class="wz-review-empty">Belum diunggah</span>
                                </template>
                            </div>
                            <div class="wz-review-item mt-3" x-show="optionalDocuments.imb.file || optionalDocuments.pbb.file">
                                <span class="wz-review-label">Dokumen pendukung</span>
                                <template x-if="optionalDocuments.imb.file">
                                    <p class="wz-review-desc">
                                        IMB / PBG: <span x-text="optionalDocuments.imb.file.name"></span>
                                    </p>
                                </template>
                                <template x-if="optionalDocuments.pbb.file">
                                    <p class="wz-review-desc">
                                        SPPT &amp; STTS PBB: <span x-text="optionalDocuments.pbb.file.name"></span>
                                    </p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Status Info --}}
                    <div class="wz-status-box">
                        <div class="wz-status-icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.834-1.964-.834-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div>
                            <p class="wz-status-title">Status akan berubah menjadi <span class="wz-status-pending">Pending
                                    Verification</span></p>
                            <p class="wz-status-desc">Setelah submit, listing Anda akan ditinjau oleh tim verifikasi
                                sebelum dipublikasikan di platform.</p>
                        </div>
                    </div>
                </div>

                {{-- Card Footer --}}
                <div class="wizard-card-footer">
                    <button type="button" @click="currentStep = 2" class="wz-btn-back">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                        </svg>
                        Kembali
                    </button>
                    <button type="button" @click="submitListing()" :disabled="isLoading"
                        class="wz-btn-primary wz-btn-submit">
                        <span x-show="!isLoading">Submit Listing</span>
                        <span x-show="isLoading" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            Mengirim...
                        </span>
                        <svg x-show="!isLoading" class="w-4 h-4" fill="none" stroke="currentColor"
                            stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== AI AUTO-FILL MODAL ===== --}}
        <div x-show="showAiModal" x-cloak id="ai-auto-fill-modal"
            class="fixed inset-0 z-[90] flex items-center justify-center p-4"
            role="dialog" aria-modal="true" aria-labelledby="ai-modal-title"
            @keydown.escape.window="showAiModal && closeAiModal()">
            <div class="absolute inset-0 bg-secondary/50 backdrop-blur-sm" @click="closeAiModal()"></div>

            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-secondary/20 bg-white shadow-2xl"
                @click.stop>
                <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 id="ai-modal-title" class="font-display text-xl font-bold text-secondary">
                                Isi Form dengan AI
                            </h3>
                            <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                Tempel informasi properti Anda. AI akan mengekstrak data yang disebutkan dan mengisi
                                formulir secara otomatis.
                            </p>
                        </div>
                        <button type="button" @click="closeAiModal()" :disabled="aiLoading"
                            aria-label="Tutup modal"
                            class="shrink-0 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-secondary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 disabled:cursor-not-allowed disabled:opacity-50">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="max-h-[calc(100dvh-14rem)] overflow-y-auto px-5 py-4 sm:px-6">
                    <label for="ai-property-text" class="wz-label">Informasi properti</label>
                    <textarea id="ai-property-text" x-ref="aiTextarea" x-model="aiInput" rows="7" maxlength="5000"
                        :disabled="aiLoading" @keydown.ctrl.enter.prevent="submitAiExtraction()"
                        @keydown.meta.enter.prevent="submitAiExtraction()"
                        placeholder="Contoh: Dijual rumah 2 lantai di Bintaro, luas bangunan 120 meter persegi, luas tanah 150 meter persegi, harga 1,8 miliar, terdiri dari 3 kamar tidur dan 2 kamar mandi."
                        class="wz-input wz-textarea min-h-[160px] resize-y"></textarea>
                    <p class="wz-helper">Minimal 10 karakter. Data yang tidak disebutkan tidak akan diisi.</p>

                    <div x-show="aiError" x-cloak role="alert"
                        class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium leading-relaxed text-red-600"
                        x-text="aiError"></div>
                </div>

                <div
                    class="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                    <button type="button" @click="closeAiModal()" :disabled="aiLoading"
                        class="wz-btn-draft justify-center sm:justify-start">
                        Batal
                    </button>
                    <button type="button" @click="submitAiExtraction()" :disabled="aiLoading"
                        class="wz-btn-primary justify-center disabled:translate-y-0 disabled:shadow-none sm:justify-start">
                        <svg x-show="aiLoading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        <span x-text="aiLoading ? 'Mengekstrak...' : 'Ekstrak Data'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== DESCRIPTION REPLACEMENT CONFIRMATION ===== --}}
        <div x-show="showDescriptionConfirm" x-cloak class="fixed inset-0 z-[90] flex items-center justify-center p-4"
            role="dialog" aria-modal="true" aria-labelledby="description-confirm-title"
            @keydown.escape.window="showDescriptionConfirm && cancelDescriptionReplacement()">
            <div class="absolute inset-0 bg-secondary/50 backdrop-blur-sm" @click="cancelDescriptionReplacement()"></div>

            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-secondary/20 bg-white shadow-2xl"
                @click.stop>
                <div class="px-5 py-5 sm:px-6">
                    <h3 id="description-confirm-title" class="font-display text-xl font-bold text-secondary">
                        Ganti deskripsi yang sudah ada?
                    </h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-500">
                        Deskripsi Anda saat ini akan diganti dengan hasil AI. Anda tetap dapat mengeditnya setelahnya.
                    </p>
                    <div class="mt-4 max-h-48 overflow-y-auto whitespace-pre-line rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-relaxed text-secondary"
                        x-text="pendingDescription"></div>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                    <button type="button" @click="cancelDescriptionReplacement()" class="wz-btn-draft justify-center">
                        Batal
                    </button>
                    <button type="button" x-ref="descriptionConfirmButton" @click="confirmDescriptionReplacement()"
                        class="wz-btn-primary justify-center">
                        Ganti deskripsi
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== SUCCESS MODAL ===== --}}
        <div x-show="showSuccess" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="wz-success-modal" x-transition:enter="transition ease-out duration-300 delay-100"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="wz-success-icon">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="wz-success-title">Listing Berhasil Dikirim!</h3>
                <p class="wz-success-desc">Listing properti Anda sedang dalam proses verifikasi. Kami akan menghubungi Anda
                    segera.</p>
                <a href="{{ route('seller.listing') }}" class="wz-btn-primary mt-6">
                    Kembali ke Dashboard
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>

        {{-- ===== FOOTER NOTE ===== --}}
        <div class="wizard-footer-note" x-show="form.property_type">
            <p>Properti belum diterbitkan. Anda dapat menyimpan dan melanjutkan di tahap berikutnya.</p>
        </div>
    </div>

    @include('listing-wizard.partials.styles')

    @include('listing-wizard.partials.scripts')

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endpush
@endsection
