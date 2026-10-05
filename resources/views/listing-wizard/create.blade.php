@extends('layouts.app')
@section('page-title', 'Pasang Properti Anda')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .leaflet-container {
            z-index: 10 !important;
            font-family: inherit;
        }
    </style>
@endpush

@section('content')
<div class="wizard-page"
     x-data="listingWizard()"
     x-cloak>

    {{-- ===== PAGE HEADER ===== --}}
    <div class="wizard-page-header">
        <a href="{{ route('seller.my-properties') }}" class="wizard-back-link">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <h1 class="wizard-page-title">Pasang properti Anda</h1>
        <div class="wizard-draft-badge" x-show="hasDraft">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Draf tersimpan
        </div>
    </div>

    {{-- ===== STEP INDICATOR ===== --}}
    <div class="wizard-steps">
        {{-- Step 1 --}}
        <div class="wizard-step-item" :class="{ 'active': currentStep === 1, 'completed': currentStep > 1 }" @click="goToStep(1)">
            <div class="wizard-step-number">
                <template x-if="currentStep > 1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="currentStep <= 1">
                    <span>1</span>
                </template>
            </div>
            <span class="wizard-step-label">Detail properti</span>
        </div>

        <div class="wizard-step-connector" :class="{ 'active': currentStep > 1 }"></div>

        {{-- Step 2 --}}
        <div class="wizard-step-item" :class="{ 'active': currentStep === 2, 'completed': currentStep > 2 }" @click="goToStep(2)">
            <div class="wizard-step-number">
                <template x-if="currentStep > 2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
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
    <div x-show="currentStep === 1"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4">

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
                            <input type="radio" name="property_type" value="bangunan" x-model="form.property_type" class="sr-only">
                            <div class="wizard-type-check" x-show="form.property_type === 'bangunan'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="wizard-type-icon bangunan">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/>
                                </svg>
                            </div>
                            <span class="wizard-type-title">Bangunan</span>
                            <span class="wizard-type-desc">Rumah, apartemen, ruko dan lainnya</span>
                        </label>

                        {{-- Tanah Card --}}
                        <label class="wizard-type-card" :class="{ 'selected': form.property_type === 'tanah' }">
                            <input type="radio" name="property_type" value="tanah" x-model="form.property_type" class="sr-only">
                            <div class="wizard-type-check" x-show="form.property_type === 'tanah'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="wizard-type-icon tanah">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
                                </svg>
                            </div>
                            <span class="wizard-type-title">Tanah</span>
                            <span class="wizard-type-desc">Kavling, lahan, dan tanah komersial</span>
                        </label>
                    </div>
                    <template x-if="errors.property_type"><p class="wz-error" x-text="errors.property_type"></p></template>
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
                                    <template x-if="errors.building_type"><p class="wz-error" x-text="errors.building_type"></p></template>
                                </div>
                                <div class="wizard-field">
                                    <label class="wz-label">Tujuan transaksi <span class="wz-required">*</span></label>
                                    <select x-model="form.transaction_type" class="wz-input">
                                        <option value="">Pilih tujuan...</option>
                                        <option value="dijual">Dijual</option>
                                        <option value="disewakan">Disewakan</option>
                                    </select>
                                    <template x-if="errors.transaction_type"><p class="wz-error" x-text="errors.transaction_type"></p></template>
                                </div>
                            </div>
                        </div>

                        {{-- Judul Properti --}}
                        <div class="wizard-section">
                            <div class="wizard-field">
                                <label class="wz-label">Judul properti <span class="wz-required">*</span></label>
                                <input type="text" x-model="form.title" placeholder="Rumah modern 3 lantai di kawasan Bintaro" class="wz-input">
                                <p class="wz-helper">Gunakan judul yang singkat, jelas, dan menggambarkan properti Anda.</p>
                                <template x-if="errors.title"><p class="wz-error" x-text="errors.title"></p></template>
                            </div>
                        </div>

                        {{-- Harga Properti --}}
                        <div class="wizard-section">
                            <div class="wizard-field">
                                <label class="wz-label">Harga properti <span class="wz-required">*</span></label>
                                <div class="wz-input-group">
                                    <span class="wz-input-prefix">Rp</span>
                                    <input type="text" x-model="form.price"
                                           @input="form.price = formatCurrency($event.target.value)"
                                           placeholder="1.850.000.000"
                                           class="wz-input has-prefix">
                                </div>
                                <p class="wz-helper">Masukkan harga total properti, bukan harga per meter persegi.</p>
                                <template x-if="errors.price"><p class="wz-error" x-text="errors.price"></p></template>
                            </div>
                            <label class="wz-checkbox-label mt-3">
                                <input type="checkbox" x-model="form.negotiable" class="wz-checkbox">
                                <span>Harga dapat dinegosiasikan</span>
                            </label>
                        </div>

                        {{-- Deskripsi Properti --}}
                        <div class="wizard-section">
                            <div class="wizard-field">
                                <label class="wz-label">Deskripsi properti <span class="wz-required">*</span></label>
                                <textarea x-model="form.description" rows="5"
                                          placeholder="Rumah modern siap huni dengan pencahayaan alam, ruang keluarga yang luas, dan taman pribadi. Berada di lingkungan yang tenang dengan keamanan 24 jam. Dekat sekolah, pusat perbelanjaan, dan akses tol bintaro."
                                          class="wz-input wz-textarea"></textarea>
                                <p class="wz-helper">Cantumkan kondisi, keunggulan, dan akses di sekitar properti.</p>
                                <template x-if="errors.description"><p class="wz-error" x-text="errors.description"></p></template>
                            </div>
                        </div>

                        {{-- ===== LOKASI PROPERTI ===== --}}
                        <div class="wizard-divider"></div>
                        <div class="wizard-section">
                            <h3 class="wizard-section-title">Lokasi properti</h3>
                            <p class="wizard-section-subtitle">Alamat yang akurat membantu calon pembeli menemukan properti Anda.</p>

                            <div class="wizard-form-grid grid-2 mt-5">
                                <div class="wizard-field">
                                    <label class="wz-label">Provinsi <span class="wz-required">*</span></label>
                                    <input type="text" x-model="form.province" placeholder="Banten" class="wz-input">
                                    <template x-if="errors.province"><p class="wz-error" x-text="errors.province"></p></template>
                                </div>
                                <div class="wizard-field">
                                    <label class="wz-label">Kota / kabupaten <span class="wz-required">*</span></label>
                                    <input type="text" x-model="form.city" placeholder="Tangerang Selatan" class="wz-input">
                                    <template x-if="errors.city"><p class="wz-error" x-text="errors.city"></p></template>
                                </div>
                            </div>
                            <div class="wizard-form-grid grid-2 mt-4">
                                <div class="wizard-field">
                                    <label class="wz-label">Kecamatan <span class="wz-required">*</span></label>
                                    <input type="text" x-model="form.district" placeholder="Pondok Aren" class="wz-input">
                                    <template x-if="errors.district"><p class="wz-error" x-text="errors.district"></p></template>
                                </div>
                                <div class="wizard-field">
                                    <label class="wz-label">Kode pos</label>
                                    <input type="text" x-model="form.postal_code" placeholder="15224" class="wz-input">
                                </div>
                            </div>

                            <div class="wizard-field mt-4">
                                <label class="wz-label">Alamat lengkap <span class="wz-required">*</span></label>
                                <input type="text" x-model="form.address" placeholder="Jl. Bintaro Utama, Sektor 9, Blok H No. 12" class="wz-input">
                                <div class="wz-helper-icon mt-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Nomor rumah tidak ditampilkan ke publik. Lokasi akan ditampilkan secara perkiraan.</span>
                                </div>
                                <template x-if="errors.address"><p class="wz-error" x-text="errors.address"></p></template>
                            </div>

                            {{-- Map --}}
                            <div class="wizard-field mt-4">
                                <label class="wz-label">Titik lokasi di peta</label>
                                <div class="wz-map-search">
                                    <input type="text"
                                           x-model="searchQuery"
                                           @keydown.enter.prevent="searchLocation()"
                                           placeholder="Cari alamat, nama jalan, atau daerah..."
                                           class="wz-input flex-1 text-sm">
                                    <button type="button"
                                            @click="searchLocation()"
                                            :disabled="isSearching"
                                            class="wz-btn-search">
                                        <span x-show="!isSearching">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </span>
                                        <span x-show="isSearching">
                                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
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
                                        <input type="number" step="0.00000001" x-model="form.latitude" placeholder="-6.12345678" class="wz-input text-sm">
                                    </div>
                                    <div class="wizard-field">
                                        <label class="wz-label-sm">Longitude</label>
                                        <input type="number" step="0.00000001" x-model="form.longitude" placeholder="106.12345678" class="wz-input text-sm">
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
                                        <input type="number" x-model="form.land_area_sqm" placeholder="120" min="0" class="wz-input has-suffix">
                                        <span class="wz-input-suffix">m²</span>
                                    </div>
                                    <template x-if="errors.land_area_sqm"><p class="wz-error" x-text="errors.land_area_sqm"></p></template>
                                </div>
                                <div class="wizard-field">
                                    <label class="wz-label">Luas bangunan <span class="wz-required">*</span></label>
                                    <div class="wz-input-group">
                                        <input type="number" x-model="form.building_area_sqm" placeholder="150" min="0" class="wz-input has-suffix">
                                        <span class="wz-input-suffix">m²</span>
                                    </div>
                                    <template x-if="errors.building_area_sqm"><p class="wz-error" x-text="errors.building_area_sqm"></p></template>
                                </div>
                            </div>

                            <div class="wizard-form-grid grid-3 mt-4">
                                <div class="wizard-field">
                                    <label class="wz-label">Kamar tidur <span class="wz-required">*</span></label>
                                    <input type="number" x-model="form.bedrooms" placeholder="3" min="0" class="wz-input">
                                    <template x-if="errors.bedrooms"><p class="wz-error" x-text="errors.bedrooms"></p></template>
                                </div>
                                <div class="wizard-field">
                                    <label class="wz-label">Kamar mandi <span class="wz-required">*</span></label>
                                    <input type="number" x-model="form.bathrooms" placeholder="2" min="0" class="wz-input">
                                    <template x-if="errors.bathrooms"><p class="wz-error" x-text="errors.bathrooms"></p></template>
                                </div>
                                <div class="wizard-field">
                                    <label class="wz-label">Jumlah lantai <span class="wz-required">*</span></label>
                                    <input type="number" x-model="form.floors" placeholder="2" min="1" class="wz-input">
                                    <template x-if="errors.floors"><p class="wz-error" x-text="errors.floors"></p></template>
                                </div>
                            </div>

                            <div class="wizard-form-grid grid-2 mt-4">
                                <div class="wizard-field">
                                    <label class="wz-label">Kondisi bangunan <span class="wz-required">*</span></label>
                                    <select x-model="form.condition" class="wz-input">
                                        <option value="">Pilih kondisi...</option>
                                        <option value="baru">Baru</option>
                                        <option value="siap_huni">Siap huni</option>
                                        <option value="butuh_renovasi">Butuh renovasi</option>
                                    </select>
                                    <template x-if="errors.condition"><p class="wz-error" x-text="errors.condition"></p></template>
                                </div>
                                <div class="wizard-field">
                                    <label class="wz-label">Jenis sertifikat <span class="wz-required">*</span></label>
                                    <select x-model="form.certificate_type" class="wz-input">
                                        <option value="">Pilih sertifikat...</option>
                                        <option value="SHM">SHM — Hak Milik</option>
                                        <option value="SHGB">SHGB — Hak Guna Bangunan</option>
                                        <option value="GIRIK">Girik</option>
                                        <option value="AJB">AJB</option>
                                        <option value="OTHER">Lainnya</option>
                                    </select>
                                    <template x-if="errors.certificate_type"><p class="wz-error" x-text="errors.certificate_type"></p></template>
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
                                    <template x-if="errors.land_type"><p class="wz-error" x-text="errors.land_type"></p></template>
                                </div>
                                <div class="wizard-field">
                                    <label class="wz-label">Tujuan transaksi <span class="wz-required">*</span></label>
                                    <select x-model="form.transaction_type" class="wz-input">
                                        <option value="">Pilih tujuan...</option>
                                        <option value="dijual">Dijual</option>
                                        <option value="disewakan">Disewakan</option>
                                    </select>
                                    <template x-if="errors.transaction_type"><p class="wz-error" x-text="errors.transaction_type"></p></template>
                                </div>
                            </div>
                        </div>

                        {{-- Judul Properti --}}
                        <div class="wizard-section">
                            <div class="wizard-field">
                                <label class="wz-label">Judul properti <span class="wz-required">*</span></label>
                                <input type="text" x-model="form.title" placeholder="Tanah kavling strategis di pinggir jalan utama" class="wz-input">
                                <p class="wz-helper">Gunakan judul yang singkat, jelas, dan menggambarkan properti Anda.</p>
                                <template x-if="errors.title"><p class="wz-error" x-text="errors.title"></p></template>
                            </div>
                        </div>

                        {{-- Harga Properti --}}
                        <div class="wizard-section">
                            <div class="wizard-field">
                                <label class="wz-label">Harga properti <span class="wz-required">*</span></label>
                                <div class="wz-input-group">
                                    <span class="wz-input-prefix">Rp</span>
                                    <input type="text" x-model="form.price"
                                           @input="form.price = formatCurrency($event.target.value)"
                                           placeholder="750.000.000"
                                           class="wz-input has-prefix">
                                </div>
                                <p class="wz-helper">Masukkan harga total tanah, bukan harga per meter persegi.</p>
                                <template x-if="errors.price"><p class="wz-error" x-text="errors.price"></p></template>
                            </div>
                            <label class="wz-checkbox-label mt-3">
                                <input type="checkbox" x-model="form.negotiable" class="wz-checkbox">
                                <span>Harga dapat dinegosiasikan</span>
                            </label>
                        </div>

                        {{-- Deskripsi Properti --}}
                        <div class="wizard-section">
                            <div class="wizard-field">
                                <label class="wz-label">Deskripsi properti <span class="wz-required">*</span></label>
                                <textarea x-model="form.description" rows="5"
                                          placeholder="Tanah kavling siap bangun dengan akses jalan lebar. Cocok untuk hunian atau investasi. Lokasi strategis dekat dengan fasilitas publik dan akses tol."
                                          class="wz-input wz-textarea"></textarea>
                                <p class="wz-helper">Cantumkan kondisi tanah, topografi, akses jalan, dan potensi pengembangan.</p>
                                <template x-if="errors.description"><p class="wz-error" x-text="errors.description"></p></template>
                            </div>
                        </div>

                        {{-- ===== LOKASI PROPERTI (Tanah) ===== --}}
                        <div class="wizard-divider"></div>
                        <div class="wizard-section">
                            <h3 class="wizard-section-title">Lokasi properti</h3>
                            <p class="wizard-section-subtitle">Alamat yang akurat membantu calon pembeli menemukan properti Anda.</p>

                            <div class="wizard-form-grid grid-2 mt-5">
                                <div class="wizard-field">
                                    <label class="wz-label">Provinsi <span class="wz-required">*</span></label>
                                    <input type="text" x-model="form.province" placeholder="Banten" class="wz-input">
                                    <template x-if="errors.province"><p class="wz-error" x-text="errors.province"></p></template>
                                </div>
                                <div class="wizard-field">
                                    <label class="wz-label">Kota / kabupaten <span class="wz-required">*</span></label>
                                    <input type="text" x-model="form.city" placeholder="Tangerang Selatan" class="wz-input">
                                    <template x-if="errors.city"><p class="wz-error" x-text="errors.city"></p></template>
                                </div>
                            </div>
                            <div class="wizard-form-grid grid-2 mt-4">
                                <div class="wizard-field">
                                    <label class="wz-label">Kecamatan <span class="wz-required">*</span></label>
                                    <input type="text" x-model="form.district" placeholder="Pondok Aren" class="wz-input">
                                    <template x-if="errors.district"><p class="wz-error" x-text="errors.district"></p></template>
                                </div>
                                <div class="wizard-field">
                                    <label class="wz-label">Kode pos</label>
                                    <input type="text" x-model="form.postal_code" placeholder="15224" class="wz-input">
                                </div>
                            </div>

                            <div class="wizard-field mt-4">
                                <label class="wz-label">Alamat lengkap <span class="wz-required">*</span></label>
                                <input type="text" x-model="form.address" placeholder="Jl. Raya Serpong, Kavling Blok A No. 5" class="wz-input">
                                <div class="wz-helper-icon mt-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Nomor rumah tidak ditampilkan ke publik. Lokasi akan ditampilkan secara perkiraan.</span>
                                </div>
                                <template x-if="errors.address"><p class="wz-error" x-text="errors.address"></p></template>
                            </div>

                            {{-- Map --}}
                            <div class="wizard-field mt-4">
                                <label class="wz-label">Titik lokasi di peta</label>
                                <div class="wz-map-search">
                                    <input type="text"
                                           x-model="searchQuery"
                                           @keydown.enter.prevent="searchLocation()"
                                           placeholder="Cari alamat, nama jalan, atau daerah..."
                                           class="wz-input flex-1 text-sm">
                                    <button type="button"
                                            @click="searchLocation()"
                                            :disabled="isSearching"
                                            class="wz-btn-search">
                                        <span x-show="!isSearching">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </span>
                                        <span x-show="isSearching">
                                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
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
                                        <input type="number" step="0.00000001" x-model="form.latitude" placeholder="-6.12345678" class="wz-input text-sm">
                                    </div>
                                    <div class="wizard-field">
                                        <label class="wz-label-sm">Longitude</label>
                                        <input type="number" step="0.00000001" x-model="form.longitude" placeholder="106.12345678" class="wz-input text-sm">
                                    </div>
                                </div>
                                <input type="hidden" id="osm_url" x-model="osmUrl">
                            </div>
                        </div>

                        {{-- ===== SPESIFIKASI TANAH ===== --}}
                        <div class="wizard-divider"></div>
                        <div class="wizard-section">
                            <h3 class="wizard-section-title">Spesifikasi tanah</h3>
                            <p class="wizard-section-subtitle">Lengkapi informasi luas, topografi, dan legalitas tanah.</p>

                            <div class="wizard-form-grid grid-2 mt-5">
                                <div class="wizard-field">
                                    <label class="wz-label">Luas tanah <span class="wz-required">*</span></label>
                                    <div class="wz-input-group">
                                        <input type="number" x-model="form.land_area_sqm" placeholder="500" min="0" class="wz-input has-suffix">
                                        <span class="wz-input-suffix">m²</span>
                                    </div>
                                    <template x-if="errors.land_area_sqm"><p class="wz-error" x-text="errors.land_area_sqm"></p></template>
                                </div>
                                <div class="wizard-field">
                                    <label class="wz-label">Lebar depan</label>
                                    <div class="wz-input-group">
                                        <input type="number" x-model="form.front_width" placeholder="20" min="0" class="wz-input has-suffix">
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
                                    <template x-if="errors.land_contour"><p class="wz-error" x-text="errors.land_contour"></p></template>
                                </div>
                                <div class="wizard-field">
                                    <label class="wz-label">Akses jalan <span class="wz-required">*</span></label>
                                    <select x-model="form.road_access" class="wz-input">
                                        <option value="">Pilih akses...</option>
                                        <option value="jalan_utama">Jalan utama</option>
                                        <option value="jalan_lingkungan">Jalan lingkungan</option>
                                        <option value="gang">Gang</option>
                                    </select>
                                    <template x-if="errors.road_access"><p class="wz-error" x-text="errors.road_access"></p></template>
                                </div>
                            </div>

                            <div class="wizard-form-grid grid-2 mt-4">
                                <div class="wizard-field">
                                    <label class="wz-label">Jenis sertifikat <span class="wz-required">*</span></label>
                                    <select x-model="form.certificate_type" class="wz-input">
                                        <option value="">Pilih sertifikat...</option>
                                        <option value="SHM">SHM — Hak Milik</option>
                                        <option value="SHGB">SHGB — Hak Guna Bangunan</option>
                                        <option value="GIRIK">Girik</option>
                                        <option value="AJB">AJB</option>
                                        <option value="OTHER">Lainnya</option>
                                    </select>
                                    <template x-if="errors.certificate_type"><p class="wz-error" x-text="errors.certificate_type"></p></template>
                                </div>
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
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    Simpan draf
                </button>
                <button type="button"
                        @click="submitStepOne()"
                        :disabled="isLoading"
                        class="wz-btn-primary">
                    <span x-show="!isLoading">Lanjut ke foto & dokumen</span>
                    <span x-show="isLoading" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Menyimpan...
                    </span>
                    <svg x-show="!isLoading" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== STEP 2: FOTO & DOKUMEN ===== --}}
    <div x-show="currentStep === 2"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4">

        <div class="wizard-card">
            <div class="wizard-card-body">

                {{-- Foto Properti --}}
                <div class="wizard-section">
                    <h3 class="wizard-section-title">Foto properti</h3>
                    <p class="wizard-section-subtitle">Upload foto-foto terbaik properti Anda. Minimal 1 foto, maksimal 10 foto.</p>

                    <div class="wz-photo-grid mt-5" id="photo-preview-grid">
                        <template x-for="(photo, index) in photoPreviews" :key="index">
                            <div class="wz-photo-item">
                                <img :src="photo" class="wz-photo-img" alt="Preview">
                                <div class="wz-photo-overlay">
                                    <button @click="removePhoto(index)" class="wz-photo-remove">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
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
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                </div>
                                <span>Tambah Foto</span>
                                <input type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden"
                                       @change="handlePhotoUpload($event)">
                            </label>
                        </template>
                    </div>
                    <template x-if="errors.photos"><p class="wz-error mt-2" x-text="errors.photos"></p></template>
                </div>

                {{-- Sertifikat & Dokumen --}}
                <div class="wizard-divider"></div>
                <div class="wizard-section">
                    <h3 class="wizard-section-title">Sertifikat & dokumen legal</h3>
                    <p class="wizard-section-subtitle">Upload sertifikat properti. File diamankan menggunakan Signed URL.</p>

                    <div class="mt-4">
                        <label class="wz-cert-upload" :class="{ 'uploaded': certificateFile }">
                            <div class="wz-cert-icon" :class="{ 'uploaded': certificateFile }">
                                <template x-if="!certificateFile">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                </template>
                                <template x-if="certificateFile">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </template>
                            </div>
                            <div class="wz-cert-info">
                                <template x-if="!certificateFile">
                                    <div>
                                        <p class="wz-cert-title">Klik untuk upload sertifikat</p>
                                        <p class="wz-cert-desc">Format: PDF, JPEG, PNG · Maks. 10MB</p>
                                    </div>
                                </template>
                                <template x-if="certificateFile">
                                    <div>
                                        <p class="wz-cert-title uploaded" x-text="certificateFile.name"></p>
                                        <p class="wz-cert-desc uploaded" x-text="formatFileSize(certificateFile.size)"></p>
                                    </div>
                                </template>
                            </div>
                            <template x-if="certificateFile">
                                <button @click.prevent="removeCertificate()" class="wz-cert-remove">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </template>
                            <input type="file" accept=".pdf,image/jpeg,image/png" class="hidden"
                                   @change="handleCertificateUpload($event)">
                        </label>
                        <template x-if="errors.certificate_file"><p class="wz-error mt-2" x-text="errors.certificate_file"></p></template>
                    </div>

                    {{-- Security Info --}}
                    <div class="wz-info-box mt-4">
                        <div class="wz-info-icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div>
                            <p class="wz-info-title">File sertifikat diamankan</p>
                            <p class="wz-info-desc">Akses ke file sertifikat menggunakan <strong>Signed Temporary URL</strong> dari Laravel yang berlaku selama 10 menit untuk keamanan dokumen sensitif.</p>
                        </div>
                    </div>
                </div>

                {{-- Agreement --}}
                <div class="wizard-section">
                    <label class="wz-checkbox-label wz-agreement">
                        <input type="checkbox" x-model="agreedTerms" class="wz-checkbox">
                        <span>Saya menyatakan bahwa data yang saya masukkan adalah <strong>benar dan valid</strong>. Foto dan sertifikat yang diunggah sesuai dengan properti yang didaftarkan.</span>
                    </label>
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="wizard-card-footer">
                <button type="button" @click="currentStep = 1" class="wz-btn-back">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                    Kembali
                </button>
                <button type="button"
                        @click="submitStepTwo()"
                        :disabled="isLoading || !agreedTerms"
                        class="wz-btn-primary">
                    <span x-show="!isLoading">Lanjutkan</span>
                    <span x-show="isLoading" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Mengunggah...
                    </span>
                    <svg x-show="!isLoading" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== STEP 3: REVIEW & SUBMIT ===== --}}
    <div x-show="currentStep === 3"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4">

        <div class="wizard-card">
            <div class="wizard-card-body">

                {{-- Review: Detail Properti --}}
                <div class="wz-review-block">
                    <div class="wz-review-header">
                        <h4>Detail properti</h4>
                        <button @click="currentStep = 1" class="wz-review-edit">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            Edit
                        </button>
                    </div>
                    <div class="wz-review-body">
                        <div class="wz-review-grid">
                            <div class="wz-review-item">
                                <span class="wz-review-label">Tipe properti</span>
                                <span class="wz-review-value" x-text="form.property_type === 'bangunan' ? 'Bangunan' : 'Tanah'"></span>
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
                                <span class="wz-review-value" x-text="form.certificate_type || '-'"></span>
                            </div>
                            <div class="wz-review-item">
                                <span class="wz-review-label">Luas tanah</span>
                                <span class="wz-review-value" x-text="(form.land_area_sqm || '0') + ' m²'"></span>
                            </div>
                            <template x-if="form.property_type === 'bangunan'">
                                <div class="wz-review-item">
                                    <span class="wz-review-label">Luas bangunan</span>
                                    <span class="wz-review-value" x-text="form.building_area_sqm ? form.building_area_sqm + ' m²' : '-'"></span>
                                </div>
                            </template>
                            <template x-if="form.property_type === 'bangunan'">
                                <div class="wz-review-item">
                                    <span class="wz-review-label">Kamar tidur / mandi</span>
                                    <span class="wz-review-value" x-text="(form.bedrooms || '0') + ' KT / ' + (form.bathrooms || '0') + ' KM'"></span>
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
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
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
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            Edit
                        </button>
                    </div>
                    <div class="wz-review-body">
                        <div class="wz-review-item">
                            <span class="wz-review-label">Foto properti</span>
                            <div class="wz-review-photos">
                                <template x-for="(photo, index) in photoPreviews" :key="'review-'+index">
                                    <div class="wz-review-photo-thumb">
                                        <img :src="photo" alt="Preview">
                                    </div>
                                </template>
                            </div>
                            <p class="wz-review-photo-count" x-text="photoPreviews.length + ' foto diunggah'"></p>
                        </div>
                        <div class="wz-review-item mt-3">
                            <span class="wz-review-label">Sertifikat</span>
                            <template x-if="certificateFile">
                                <div class="wz-review-cert-badge">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span x-text="certificateFile.name"></span>
                                </div>
                            </template>
                            <template x-if="!certificateFile">
                                <span class="wz-review-empty">Belum diunggah</span>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Status Info --}}
                <div class="wz-status-box">
                    <div class="wz-status-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.834-1.964-.834-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <div>
                        <p class="wz-status-title">Status akan berubah menjadi <span class="wz-status-pending">Pending Verification</span></p>
                        <p class="wz-status-desc">Setelah submit, listing Anda akan ditinjau oleh tim verifikasi sebelum dipublikasikan di platform.</p>
                    </div>
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="wizard-card-footer">
                <button type="button" @click="currentStep = 2" class="wz-btn-back">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                    Kembali
                </button>
                <button type="button"
                        @click="submitListing()"
                        :disabled="isLoading"
                        class="wz-btn-primary wz-btn-submit">
                    <span x-show="!isLoading">Submit Listing</span>
                    <span x-show="isLoading" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Mengirim...
                    </span>
                    <svg x-show="!isLoading" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== SUCCESS MODAL ===== --}}
    <div x-show="showSuccess"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="wz-success-modal"
             x-transition:enter="transition ease-out duration-300 delay-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="wz-success-icon">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="wz-success-title">Listing Berhasil Dikirim!</h3>
            <p class="wz-success-desc">Listing properti Anda sedang dalam proses verifikasi. Kami akan menghubungi Anda segera.</p>
            <a href="{{ route('dashboard') }}" class="wz-btn-primary mt-6">
                Kembali ke Dashboard
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </div>

    {{-- ===== FOOTER NOTE ===== --}}
    <div class="wizard-footer-note" x-show="form.property_type">
        <p>Properti belum diterbitkan. Anda dapat menyimpan dan melanjutkan di tahap berikutnya.</p>
    </div>
</div>

{{-- ===== STYLES ===== --}}
<style>
    /* ===== PAGE LAYOUT ===== */
    .wizard-page {
        max-width: 720px;
        margin: 0 auto;
        padding: 16px 8px 48px;
        font-family: 'Manrope', sans-serif;
    }
    @media (min-width: 640px) {
        .wizard-page { padding: 32px 0 64px; }
    }

    /* ===== PAGE HEADER ===== */
    .wizard-page-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }
    .wizard-back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
        text-decoration: none;
        transition: color 0.2s;
    }
    .wizard-back-link:hover { color: #2C3E50; }
    .wizard-page-title {
        font-family: 'Outfit', sans-serif;
        font-size: 22px;
        font-weight: 700;
        color: #2C3E50;
        flex: 1;
        text-align: center;
    }
    @media (min-width: 640px) {
        .wizard-page-title { font-size: 26px; }
    }
    .wizard-draft-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        white-space: nowrap;
    }

    /* ===== STEP INDICATOR ===== */
    .wizard-steps {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        margin-bottom: 32px;
    }
    .wizard-step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        cursor: default;
        transition: all 0.3s;
    }
    .wizard-step-item.completed { cursor: pointer; }
    .wizard-step-number {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Outfit', sans-serif;
        font-size: 14px;
        font-weight: 700;
        background: #e2e8f0;
        color: #94a3b8;
        transition: all 0.3s;
    }
    .wizard-step-item.active .wizard-step-number {
        background: #FC4907;
        color: #fff;
        box-shadow: 0 4px 14px rgba(252, 73, 7, 0.3);
    }
    .wizard-step-item.completed .wizard-step-number {
        background: #10b981;
        color: #fff;
    }
    .wizard-step-label {
        font-size: 12px;
        font-weight: 600;
        color: #94a3b8;
        white-space: nowrap;
    }
    .wizard-step-item.active .wizard-step-label { color: #FC4907; font-weight: 700; }
    .wizard-step-item.completed .wizard-step-label { color: #10b981; }
    @media (max-width: 639px) {
        .wizard-step-label { font-size: 11px; }
    }
    .wizard-step-connector {
        width: 48px;
        height: 3px;
        border-radius: 3px;
        background: #e2e8f0;
        margin: 0 8px;
        margin-bottom: 24px;
        transition: background 0.4s;
    }
    .wizard-step-connector.active { background: #10b981; }
    @media (min-width: 640px) {
        .wizard-step-connector { width: 80px; }
    }

    /* ===== CARD ===== */
    .wizard-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.02);
        overflow: hidden;
    }
    .wizard-card-body {
        padding: 24px;
    }
    @media (min-width: 640px) {
        .wizard-card-body { padding: 32px; }
    }

    /* ===== CARD FOOTER ===== */
    .wizard-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
    }
    @media (min-width: 640px) {
        .wizard-card-footer { padding: 16px 32px; }
    }

    /* ===== SECTIONS ===== */
    .wizard-section { margin-bottom: 24px; }
    .wizard-section:last-child { margin-bottom: 0; }
    .wizard-sections-wrapper { display: contents; }
    .wizard-section-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .wizard-section-title {
        font-family: 'Outfit', sans-serif;
        font-size: 18px;
        font-weight: 700;
        color: #2C3E50;
    }
    .wizard-section-subtitle {
        font-size: 13px;
        color: #94a3b8;
        margin-top: 4px;
        line-height: 1.5;
    }
    .wizard-divider {
        height: 1px;
        background: #f1f5f9;
        margin: 28px 0;
    }

    /* ===== TYPE SELECTOR CARDS ===== */
    .wizard-type-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .wizard-type-card {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 24px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.25s ease;
        background: #fff;
    }
    .wizard-type-card:hover {
        border-color: #cbd5e1;
        background: #fafbfc;
        transform: translateY(-1px);
    }
    .wizard-type-card.selected {
        border-color: #FC4907;
        background: #FFF7F4;
        box-shadow: 0 0 0 3px rgba(252, 73, 7, 0.1);
    }
    .wizard-type-check {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #FC4907;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .wizard-type-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        transition: all 0.25s;
    }
    .wizard-type-icon.bangunan {
        background: #FFF0EB;
        color: #FC4907;
    }
    .wizard-type-card.selected .wizard-type-icon.bangunan {
        background: #FC4907;
        color: #fff;
    }
    .wizard-type-icon.tanah {
        background: #f0f4f8;
        color: #64748b;
    }
    .wizard-type-card.selected .wizard-type-icon.tanah {
        background: #FC4907;
        color: #fff;
    }
    .wizard-type-title {
        font-family: 'Outfit', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: #2C3E50;
        margin-bottom: 4px;
    }
    .wizard-type-card.selected .wizard-type-title { color: #FC4907; }
    .wizard-type-desc {
        font-size: 12px;
        color: #94a3b8;
        line-height: 1.4;
    }

    /* ===== FORM GRIDS ===== */
    .wizard-form-grid {
        display: grid;
        gap: 16px;
    }
    .wizard-form-grid.grid-2 { grid-template-columns: 1fr 1fr; }
    .wizard-form-grid.grid-3 { grid-template-columns: 1fr 1fr 1fr; }
    @media (max-width: 639px) {
        .wizard-form-grid.grid-3 { grid-template-columns: 1fr 1fr; }
    }

    /* ===== FORM FIELDS ===== */
    .wizard-field { display: flex; flex-direction: column; }
    .wz-label {
        font-size: 14px;
        font-weight: 600;
        color: #2C3E50;
        margin-bottom: 6px;
    }
    .wz-label-sm {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 4px;
    }
    .wz-required { color: #FC4907; }
    .wz-hint { font-size: 13px; color: #94a3b8; }

    /* ===== INPUT ===== */
    .wz-input {
        display: block;
        width: 100%;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: #fff;
        padding: 10px 14px;
        font-size: 14px;
        font-weight: 500;
        font-family: 'Manrope', sans-serif;
        color: #2C3E50;
        outline: none;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .wz-input::placeholder { color: #94a3b8; font-weight: 400; }
    .wz-input:focus {
        border-color: #FC4907;
        box-shadow: 0 0 0 3px rgba(252, 73, 7, 0.1), 0 1px 2px rgba(0,0,0,0.03);
    }
    .wz-input:disabled {
        cursor: not-allowed;
        background: #f8fafc;
        color: #94a3b8;
    }
    select.wz-input {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 12px center;
        background-repeat: no-repeat;
        background-size: 20px 20px;
        padding-right: 40px;
    }
    .wz-textarea { resize: none; line-height: 1.6; }

    /* ===== INPUT GROUP ===== */
    .wz-input-group {
        position: relative;
        display: flex;
        align-items: center;
    }
    .wz-input-prefix {
        position: absolute;
        left: 14px;
        font-size: 14px;
        font-weight: 600;
        color: #94a3b8;
        pointer-events: none;
        z-index: 1;
    }
    .wz-input.has-prefix { padding-left: 40px; }
    .wz-input-suffix {
        position: absolute;
        right: 14px;
        font-size: 13px;
        font-weight: 600;
        color: #94a3b8;
        pointer-events: none;
        z-index: 1;
    }
    .wz-input.has-suffix { padding-right: 40px; }

    /* ===== HELPER TEXT ===== */
    .wz-helper {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 6px;
        line-height: 1.5;
    }
    .wz-helper-icon {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        font-size: 12px;
        color: #94a3b8;
        line-height: 1.5;
    }
    .wz-error {
        font-size: 12px;
        font-weight: 600;
        color: #ef4444;
        margin-top: 4px;
    }

    /* ===== CHECKBOX ===== */
    .wz-checkbox-label {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        cursor: pointer;
        user-select: none;
    }
    .wz-checkbox {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        border: 1.5px solid #cbd5e1;
        accent-color: #FC4907;
        cursor: pointer;
        margin-top: 2px;
        flex-shrink: 0;
    }
    .wz-checkbox-label span {
        font-size: 14px;
        color: #64748b;
        line-height: 1.5;
    }
    .wz-agreement span { font-size: 13px; }
    .wz-agreement strong { color: #2C3E50; }

    /* ===== MAP ===== */
    .wz-map-search {
        display: flex;
        gap: 8px;
    }
    .wz-btn-search {
        padding: 10px 14px;
        background: #2C3E50;
        color: #fff;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
    }
    .wz-btn-search:hover { background: #1a252f; }
    .wz-btn-search:disabled { opacity: 0.5; cursor: not-allowed; }
    .wz-map-container {
        border-radius: 12px;
        overflow: hidden;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
    }
    .wz-map {
        width: 100%;
        height: 256px;
    }
    @media (min-width: 640px) {
        .wz-map { height: 320px; }
    }

    /* ===== BUTTONS ===== */
    .wz-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 24px;
        background: #FC4907;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        font-family: 'Manrope', sans-serif;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.25s;
        box-shadow: 0 4px 12px rgba(252, 73, 7, 0.25);
        white-space: nowrap;
    }
    .wz-btn-primary:hover {
        background: #e04006;
        box-shadow: 0 6px 20px rgba(252, 73, 7, 0.35);
        transform: translateY(-1px);
    }
    .wz-btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    .wz-btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        background: transparent;
        color: #64748b;
        font-size: 14px;
        font-weight: 600;
        font-family: 'Manrope', sans-serif;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .wz-btn-back:hover { background: #f1f5f9; color: #2C3E50; }
    .wz-btn-draft {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        background: transparent;
        color: #64748b;
        font-size: 14px;
        font-weight: 600;
        font-family: 'Manrope', sans-serif;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .wz-btn-draft:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #2C3E50;
    }

    /* ===== PHOTO GRID ===== */
    .wz-photo-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    @media (min-width: 640px) {
        .wz-photo-grid { grid-template-columns: repeat(4, 1fr); }
    }
    .wz-photo-item {
        position: relative;
        aspect-ratio: 4/3;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e2e8f0;
        background: #f8fafc;
    }
    .wz-photo-img { width: 100%; height: 100%; object-fit: cover; }
    .wz-photo-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .wz-photo-item:hover .wz-photo-overlay { opacity: 1; }
    .wz-photo-remove {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,0.9);
        color: #ef4444;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .wz-photo-remove:hover { background: #ef4444; color: #fff; }
    .wz-photo-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        padding: 2px 8px;
        background: #FC4907;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .wz-photo-add {
        aspect-ratio: 4/3;
        border-radius: 12px;
        border: 2px dashed #cbd5e1;
        background: #fafbfc;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .wz-photo-add:hover {
        border-color: #FC4907;
        background: #FFF7F4;
    }
    .wz-photo-add-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(252, 73, 7, 0.08);
        color: #FC4907;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    .wz-photo-add:hover .wz-photo-add-icon { background: rgba(252, 73, 7, 0.15); }
    .wz-photo-add span {
        font-size: 12px;
        font-weight: 600;
        color: #94a3b8;
        transition: color 0.2s;
    }
    .wz-photo-add:hover span { color: #FC4907; }

    /* ===== CERTIFICATE UPLOAD ===== */
    .wz-cert-upload {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
        background: #fafbfc;
    }
    .wz-cert-upload:hover {
        border-color: #FC4907;
        background: #FFF7F4;
    }
    .wz-cert-upload.uploaded {
        border-color: #10b981;
        border-style: solid;
        background: #f0fdf4;
    }
    .wz-cert-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: rgba(252, 73, 7, 0.08);
        color: #FC4907;
        transition: all 0.2s;
    }
    .wz-cert-upload:hover .wz-cert-icon:not(.uploaded) { background: rgba(252, 73, 7, 0.15); }
    .wz-cert-icon.uploaded { background: #dcfce7; color: #10b981; }
    .wz-cert-info { flex: 1; min-width: 0; }
    .wz-cert-title {
        font-size: 14px;
        font-weight: 600;
        color: #2C3E50;
    }
    .wz-cert-title.uploaded { color: #166534; }
    .wz-cert-desc {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 2px;
    }
    .wz-cert-desc.uploaded { color: #22c55e; }
    .wz-cert-remove {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #fee2e2;
        color: #ef4444;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s;
    }
    .wz-cert-remove:hover { background: #fecaca; }

    /* ===== INFO BOX ===== */
    .wz-info-box {
        display: flex;
        gap: 12px;
        padding: 14px 16px;
        background: linear-gradient(135deg, rgba(252, 73, 7, 0.04), rgba(252, 73, 7, 0.08));
        border: 1px solid rgba(252, 73, 7, 0.12);
        border-radius: 12px;
    }
    .wz-info-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(252, 73, 7, 0.12);
        color: #FC4907;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .wz-info-title { font-size: 13px; font-weight: 700; color: #2C3E50; }
    .wz-info-desc { font-size: 12px; color: #64748b; margin-top: 2px; line-height: 1.5; }
    .wz-info-desc strong { color: #FC4907; }

    /* ===== REVIEW BLOCKS ===== */
    .wz-review-block {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 16px;
    }
    .wz-review-block:last-of-type { margin-bottom: 0; }
    .wz-review-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
    }
    .wz-review-header h4 {
        font-family: 'Outfit', sans-serif;
        font-size: 14px;
        font-weight: 700;
        color: #2C3E50;
    }
    .wz-review-edit {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        font-weight: 600;
        color: #FC4907;
        background: none;
        border: none;
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .wz-review-edit:hover { opacity: 0.7; text-decoration: underline; }
    .wz-review-body { padding: 16px 20px; }
    .wz-review-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px 32px;
    }
    .wz-review-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .wz-review-value {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #2C3E50;
        margin-top: 2px;
    }
    .wz-review-value.wz-price { color: #FC4907; }
    .wz-review-desc {
        font-size: 13px;
        color: #64748b;
        line-height: 1.6;
        margin-top: 4px;
    }
    .wz-review-photos {
        display: flex;
        gap: 8px;
        margin-top: 8px;
        overflow-x: auto;
        padding-bottom: 4px;
    }
    .wz-review-photo-thumb {
        flex-shrink: 0;
        width: 72px;
        height: 56px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .wz-review-photo-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .wz-review-photo-count { font-size: 12px; color: #94a3b8; margin-top: 6px; }
    .wz-review-cert-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        margin-top: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #166534;
    }
    .wz-review-cert-badge svg { color: #22c55e; }
    .wz-review-empty { font-size: 13px; color: #94a3b8; margin-top: 4px; display: block; }

    /* ===== STATUS BOX ===== */
    .wz-status-box {
        display: flex;
        gap: 12px;
        padding: 14px 16px;
        background: linear-gradient(135deg, #fffbeb, rgba(252, 73, 7, 0.04));
        border: 1px solid rgba(217, 119, 6, 0.15);
        border-radius: 12px;
        margin-top: 20px;
    }
    .wz-status-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #fef3c7;
        color: #d97706;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .wz-status-title { font-size: 13px; font-weight: 700; color: #2C3E50; }
    .wz-status-pending { color: #d97706; }
    .wz-status-desc { font-size: 12px; color: #64748b; margin-top: 2px; line-height: 1.5; }

    /* ===== SUCCESS MODAL ===== */
    .wz-success-modal {
        background: #fff;
        border-radius: 20px;
        padding: 40px 32px;
        max-width: 400px;
        width: calc(100% - 32px);
        text-align: center;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
    .wz-success-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: #dcfce7;
        color: #22c55e;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .wz-success-title {
        font-family: 'Outfit', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: #2C3E50;
    }
    .wz-success-desc {
        font-size: 14px;
        color: #64748b;
        margin-top: 8px;
        line-height: 1.6;
    }

    /* ===== FOOTER NOTE ===== */
    .wizard-footer-note {
        text-align: center;
        padding: 24px 0 0;
    }
    .wizard-footer-note p {
        font-size: 12px;
        color: #94a3b8;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .wizard-sections-wrapper > .wizard-section {
        animation: fadeInUp 0.35s ease-out forwards;
    }
    .wizard-sections-wrapper > .wizard-section:nth-child(2) { animation-delay: 0.05s; }
    .wizard-sections-wrapper > .wizard-section:nth-child(3) { animation-delay: 0.1s; }
    .wizard-sections-wrapper > .wizard-divider + .wizard-section { animation-delay: 0.15s; }
</style>

{{-- ===== JAVASCRIPT ===== --}}
<script>
function listingWizard() {
    return {
        currentStep: 1,
        propertyId: null,
        isLoading: false,
        showSuccess: false,
        agreedTerms: false,
        hasDraft: false,

        // Search state
        searchQuery: '',
        isSearching: false,
        searchError: '',
        osmUrl: '',
        mapInstance: null,
        mapMarker: null,
        mapContainerId: null,

        form: {
            property_type: '',
            building_type: '',
            land_type: '',
            transaction_type: '',
            title: '',
            description: '',
            price: '',
            negotiable: false,
            land_area_sqm: '',
            building_area_sqm: '',
            bedrooms: '',
            bathrooms: '',
            floors: '',
            condition: '',
            certificate_type: '',
            province: '',
            city: '',
            district: '',
            postal_code: '',
            address: '',
            latitude: '',
            longitude: '',
            // Tanah-specific
            front_width: '',
            land_contour: '',
            road_access: '',
            zone_type: '',
        },

        errors: {},
        photoFiles: [],
        photoPreviews: [],
        certificateFile: null,

        formatCurrency(value) {
            let num = value.replace(/\D/g, '');
            return num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },

        formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        },

        init() {
            this.$watch('form.property_type', (value) => {
                if (this.mapInstance) {
                    this.mapInstance.remove();
                    this.mapInstance = null;
                    this.mapMarker = null;
                }
            });
        },

        async searchLocation() {
            if (!this.searchQuery.trim()) return;

            this.isSearching = true;
            this.searchError = '';

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchQuery)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'User-Agent': 'LaporPakApp/1.0 (Contact: admin@laporpak.com)'
                    }
                });

                if (!response.ok) throw new Error('Network response was not ok');

                const data = await response.json();

                if (data && data.length > 0) {
                    const result = data[0];
                    this.form.latitude = parseFloat(result.lat).toFixed(8);
                    this.form.longitude = parseFloat(result.lon).toFixed(8);
                } else {
                    this.searchError = 'Lokasi tidak ditemukan. Coba kata kunci yang lebih spesifik.';
                }
            } catch (error) {
                console.error('Geocoding error:', error);
                this.searchError = 'Terjadi kesalahan koneksi saat mencari lokasi.';
            } finally {
                this.isSearching = false;
            }
        },

        handlePhotoUpload(event) {
            const files = Array.from(event.target.files);
            const remaining = 10 - this.photoPreviews.length;
            const toAdd = files.slice(0, remaining);

            toAdd.forEach(file => {
                if (!file.type.match(/^image\/(jpeg|png|webp)$/)) return;
                if (file.size > 5 * 1024 * 1024) return;

                this.photoFiles.push(file);
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.photoPreviews.push(e.target.result);
                };
                reader.readAsDataURL(file);
            });

            event.target.value = '';
        },

        removePhoto(index) {
            this.photoFiles.splice(index, 1);
            this.photoPreviews.splice(index, 1);
        },

        handleCertificateUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (file.size > 10 * 1024 * 1024) {
                this.errors.certificate_file = 'Ukuran sertifikat maksimal 10MB.';
                return;
            }
            this.certificateFile = file;
            this.errors.certificate_file = null;
            event.target.value = '';
        },

        removeCertificate() {
            this.certificateFile = null;
        },

        goToStep(step) {
            if (step < this.currentStep) {
                this.currentStep = step;
            }
        },

        saveDraft() {
            this.hasDraft = true;
            // Draft save logic would go here
        },

        async submitStepOne() {
            this.errors = {};
            this.isLoading = true;

            const priceNumeric = this.form.price.replace(/\./g, '');

            try {
                const response = await fetch('{{ route("listing.step-one") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        ...this.form,
                        price: priceNumeric,
                        url_maps: this.osmUrl,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.errors) {
                        const flatErrors = {};
                        for (const [key, messages] of Object.entries(data.errors)) {
                            flatErrors[key] = messages[0];
                        }
                        this.errors = flatErrors;
                    }
                    return;
                }

                this.currentStep = 2;
                window.scrollTo({ top: 0, behavior: 'smooth' });

            } catch (error) {
                console.error('Step 1 error:', error);
                this.errors.general = 'Terjadi kesalahan. Silakan coba lagi.';
            } finally {
                this.isLoading = false;
            }
        },

        async submitStepTwo() {
            this.errors = {};

            if (this.photoFiles.length === 0) {
                this.errors.photos = 'Minimal 1 foto properti wajib diunggah.';
                return;
            }
            if (!this.certificateFile) {
                this.errors.certificate_file = 'File sertifikat wajib diunggah.';
                return;
            }
            if (!this.agreedTerms) return;

            this.isLoading = true;

            try {
                const formData = new FormData();
                this.photoFiles.forEach(file => formData.append('photos[]', file));
                formData.append('certificate_file', this.certificateFile);

                const url = '{{ route("listing.step-two") }}';

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.errors) {
                        const flatErrors = {};
                        for (const [key, messages] of Object.entries(data.errors)) {
                            flatErrors[key] = messages[0];
                        }
                        this.errors = flatErrors;
                    }
                    return;
                }

                this.currentStep = 3;
                window.scrollTo({ top: 0, behavior: 'smooth' });

            } catch (error) {
                console.error('Step 2 error:', error);
                this.errors.general = 'Terjadi kesalahan saat upload. Silakan coba lagi.';
            } finally {
                this.isLoading = false;
            }
        },

        async submitListing() {
            this.isLoading = true;

            try {
                const url = '{{ route("listing.submit") }}';

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    console.error('Submit error:', data);
                    return;
                }

                this.showSuccess = true;

            } catch (error) {
                console.error('Submit error:', error);
            } finally {
                this.isLoading = false;
            }
        },

        initMap(containerId) {
            if (this.mapInstance) {
                this.mapInstance.remove();
                this.mapInstance = null;
            }

            this.mapContainerId = containerId || 'wizard-map';
            const defaultLat = -6.2088;
            const defaultLng = 106.8456;

            setTimeout(() => {
                const container = document.getElementById(this.mapContainerId);
                if (!container) return;

                this.mapInstance = L.map(this.mapContainerId).setView([defaultLat, defaultLng], 12);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(this.mapInstance);

                setTimeout(() => {
                    if (this.mapInstance) {
                        this.mapInstance.invalidateSize();
                    }
                }, 100);

                if (this.form.latitude && this.form.longitude) {
                    this.createDraggableMarker([this.form.latitude, this.form.longitude]);
                    this.mapInstance.setView([this.form.latitude, this.form.longitude], 15);
                    this.updateOsmUrl(this.form.latitude, this.form.longitude);
                }

                this.mapInstance.on('click', (e) => {
                    this.setSelectedLocation(e.latlng);
                });

                this.$watch('form.latitude', (val) => this.updateMarkerFromInputs());
                this.$watch('form.longitude', (val) => this.updateMarkerFromInputs());

            }, 200);
        },

        updateMarkerFromInputs() {
            const lat = parseFloat(this.form.latitude);
            const lng = parseFloat(this.form.longitude);

            if (!isNaN(lat) && !isNaN(lng)) {
                const latlng = [lat, lng];
                if (this.mapMarker) {
                    this.mapMarker.setLatLng(latlng);
                } else {
                    this.createDraggableMarker(latlng);
                }
                this.updateOsmUrl(lat, lng);
                if (this.mapInstance) {
                    this.mapInstance.setView(latlng, 15);
                }
            }
        },

        createDraggableMarker(latlng) {
            if (!this.mapInstance) return;
            this.mapMarker = L.marker(latlng, { draggable: true }).addTo(this.mapInstance);
            this.mapMarker.on('dragend', (event) => {
                this.setSelectedLocation(event.target.getLatLng());
            });
        },

        setSelectedLocation(latlng) {
            const latitude = latlng.lat;
            const longitude = latlng.lng;

            this.form.latitude = latitude.toFixed(8);
            this.form.longitude = longitude.toFixed(8);

            if (this.mapMarker) {
                this.mapMarker.setLatLng(latlng);
            } else {
                this.createDraggableMarker(latlng);
            }

            this.updateOsmUrl(latitude, longitude);
        },

        updateOsmUrl(latitude, longitude) {
            this.osmUrl = `https://www.openstreetmap.org/?mlat=${latitude}&mlon=${longitude}#map=16/${latitude}/${longitude}`;
        }
    };
}
</script>

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush
@endsection