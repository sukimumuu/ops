@extends('layouts.app')
@section('page-title', 'Buat Listing Properti')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        .leaflet-container {
            z-index: 10 !important;
            font-family: inherit;
        }
    </style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto py-4 sm:py-8 px-2 sm:px-0"
     x-data="listingWizard()"
     x-cloak>
    <x-button-dashboard variant="secondary" size="sm">
        <a href="{{ route('seller.my-properties') }}">Kembali</a>
    </x-button-dashboard>
    {{-- ===== STEP INDICATOR ===== --}}
    <div class="flex items-center justify-center gap-0 mb-8 sm:mb-10">
        {{-- Step 1 --}}
        <div class="flex items-center">
            <button @click="goToStep(1)"
                    :disabled="currentStep < 1"
                    class="flex items-center gap-2 px-3 py-2 rounded-full text-sm font-bold transition-all duration-300 focus:outline-none"
                    :class="currentStep === 1
                        ? 'bg-primary text-white shadow-lg shadow-primary/30'
                        : (currentStep > 1
                            ? 'bg-emerald-500 text-white cursor-pointer hover:bg-emerald-600'
                            : 'bg-slate-200 text-slate-400 cursor-not-allowed')">
                <span class="flex items-center justify-center w-7 h-7 rounded-full text-xs font-extrabold"
                      :class="currentStep > 1 ? 'bg-white/20' : (currentStep === 1 ? 'bg-white/20' : '')">
                    <template x-if="currentStep > 1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </template>
                    <template x-if="currentStep <= 1">
                        <span>1</span>
                    </template>
                </span>
                <span class="hidden sm:inline">Informasi Properti</span>
            </button>
        </div>

        {{-- Connector --}}
        <div class="w-8 sm:w-16 h-1 rounded-full mx-1 transition-all duration-500"
             :class="currentStep > 1 ? 'bg-emerald-400' : 'bg-slate-200'"></div>

        {{-- Step 2 --}}
        <div class="flex items-center">
            <button @click="goToStep(2)"
                    :disabled="currentStep < 2"
                    class="flex items-center gap-2 px-3 py-2 rounded-full text-sm font-bold transition-all duration-300 focus:outline-none"
                    :class="currentStep === 2
                        ? 'bg-primary text-white shadow-lg shadow-primary/30'
                        : (currentStep > 2
                            ? 'bg-emerald-500 text-white cursor-pointer hover:bg-emerald-600'
                            : 'bg-slate-200 text-slate-400 cursor-not-allowed')">
                <span class="flex items-center justify-center w-7 h-7 rounded-full text-xs font-extrabold"
                      :class="currentStep > 2 ? 'bg-white/20' : (currentStep === 2 ? 'bg-white/20' : '')">
                    <template x-if="currentStep > 2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </template>
                    <template x-if="currentStep <= 2">
                        <span>2</span>
                    </template>
                </span>
                <span class="hidden sm:inline">Foto & Dokumen</span>
            </button>
        </div>

        {{-- Connector --}}
        <div class="w-8 sm:w-16 h-1 rounded-full mx-1 transition-all duration-500"
             :class="currentStep > 2 ? 'bg-emerald-400' : 'bg-slate-200'"></div>

        {{-- Step 3 --}}
        <div class="flex items-center">
            <button :disabled="currentStep < 3"
                    class="flex items-center gap-2 px-3 py-2 rounded-full text-sm font-bold transition-all duration-300 focus:outline-none"
                    :class="currentStep === 3
                        ? 'bg-primary text-white shadow-lg shadow-primary/30'
                        : 'bg-slate-200 text-slate-400 cursor-not-allowed'">
                <span class="flex items-center justify-center w-7 h-7 rounded-full text-xs font-extrabold"
                      :class="currentStep === 3 ? 'bg-white/20' : ''">
                    3
                </span>
                <span class="hidden sm:inline">Review & Submit</span>
            </button>
        </div>
    </div>

    {{-- ===== STEP 1: INFORMASI PROPERTI ===== --}}
    <div x-show="currentStep === 1"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-x-8"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-8">

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            {{-- Card Header --}}
            <div class="px-6 sm:px-8 pt-6 sm:pt-8 pb-4 border-b border-slate-100">
                <h2 class="text-xl sm:text-2xl font-extrabold text-secondary font-display">Informasi Properti</h2>
                <p class="text-sm text-slate-500 mt-1">Isi detail informasi properti yang ingin Anda jual.</p>
            </div>

            {{-- Card Body --}}
            <div class="px-6 sm:px-8 py-6 space-y-6">

                {{-- Tipe & Judul --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-secondary mb-1.5">Tipe Properti <span class="text-primary">*</span></label>
                        <select x-model="form.type"
                                class="wizard-input">
                            <option value="">Pilih tipe...</option>
                            <option value="residential">Residensial</option>
                            <option value="land">Tanah</option>
                        </select>
                        <template x-if="errors.type"><p class="mt-1 text-xs font-semibold text-red-500" x-text="errors.type"></p></template>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-secondary mb-1.5">Judul Listing <span class="text-primary">*</span></label>
                        <input type="text" x-model="form.title" placeholder="Cth: Rumah Mewah 2 Lantai di BSD"
                               class="wizard-input">
                        <template x-if="errors.title"><p class="mt-1 text-xs font-semibold text-red-500" x-text="errors.title"></p></template>
                    </div>
                </div>

                {{-- Harga & Luas --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-secondary mb-1.5">Harga (Rp) <span class="text-primary">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-bold">Rp</span>
                            <input type="text" x-model="form.price"
                                   @input="form.price = formatCurrency($event.target.value)"
                                   placeholder="0"
                                   class="wizard-input pl-10">
                        </div>
                        <template x-if="errors.price"><p class="mt-1 text-xs font-semibold text-red-500" x-text="errors.price"></p></template>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-secondary mb-1.5">Luas Tanah (m²) <span class="text-primary">*</span></label>
                        <input type="number" x-model="form.land_area_sqm" placeholder="0" min="0"
                               class="wizard-input">
                        <template x-if="errors.land_area_sqm"><p class="mt-1 text-xs font-semibold text-red-500" x-text="errors.land_area_sqm"></p></template>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-secondary mb-1.5">Luas Bangunan (m²)</label>
                        <input type="number" x-model="form.building_area_sqm" placeholder="0" min="0"
                               class="wizard-input">
                    </div>
                </div>

                {{-- Sertifikat --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-secondary mb-1.5">Tipe Sertifikat <span class="text-primary">*</span></label>
                        <select x-model="form.certificate_type" class="wizard-input">
                            <option value="">Pilih sertifikat...</option>
                            <option value="SHM">SHM (Sertifikat Hak Milik)</option>
                            <option value="SHGB">SHGB (Sertifikat Hak Guna Bangunan)</option>
                            <option value="GIRIK">Girik</option>
                            <option value="OTHER">Lainnya</option>
                        </select>
                        <template x-if="errors.certificate_type"><p class="mt-1 text-xs font-semibold text-red-500" x-text="errors.certificate_type"></p></template>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-sm font-bold text-secondary mb-1.5">Deskripsi Properti <span class="text-primary">*</span></label>
                    <textarea x-model="form.description" rows="4"
                              placeholder="Jelaskan detail properti Anda: fasilitas, kondisi, akses jalan, dll."
                              class="wizard-input resize-none"></textarea>
                    <template x-if="errors.description"><p class="mt-1 text-xs font-semibold text-red-500" x-text="errors.description"></p></template>
                </div>

                {{-- Lokasi Header --}}
                <div class="pt-2">
                    <h3 class="text-lg font-extrabold text-secondary font-display">Lokasi Properti</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Tentukan lokasi properti Anda dengan tepat.</p>
                </div>

                {{-- Alamat --}}
                <div>
                    <label class="block text-sm font-bold text-secondary mb-1.5">Alamat Lengkap <span class="text-primary">*</span></label>
                    <textarea x-model="form.address" rows="2"
                              placeholder="Cth: Jl. Raya Serpong No. 12, RT 03/RW 05"
                              class="wizard-input resize-none"></textarea>
                    <template x-if="errors.address"><p class="mt-1 text-xs font-semibold text-red-500" x-text="errors.address"></p></template>
                </div>

                {{-- Provinsi, Kota, Kecamatan --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-secondary mb-1.5">Provinsi <span class="text-primary">*</span></label>
                        <input type="text" x-model="form.province" placeholder="Cth: Jawa Barat"
                               class="wizard-input">
                        <template x-if="errors.province"><p class="mt-1 text-xs font-semibold text-red-500" x-text="errors.province"></p></template>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-secondary mb-1.5">Kota / Kabupaten <span class="text-primary">*</span></label>
                        <input type="text" x-model="form.city" placeholder="Cth: Tangerang Selatan"
                               class="wizard-input">
                        <template x-if="errors.city"><p class="mt-1 text-xs font-semibold text-red-500" x-text="errors.city"></p></template>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-secondary mb-1.5">Kecamatan <span class="text-primary">*</span></label>
                        <input type="text" x-model="form.district" placeholder="Cth: Serpong"
                               class="wizard-input">
                        <template x-if="errors.district"><p class="mt-1 text-xs font-semibold text-red-500" x-text="errors.district"></p></template>
                    </div>
                </div>

                {{-- Map & Search Integration --}}
                <div>
                    <label class="block text-sm font-bold text-secondary mb-1.5">Titik Lokasi di Peta</label>
                    
                    {{-- Form Pencarian Nominatim OSM --}}
                    <div class="flex gap-2 mb-3">
                        <input type="text" 
                               x-model="searchQuery" 
                               @keydown.enter.prevent="searchLocation()"
                               placeholder="Cari alamat, nama jalan, atau daerah..."
                               class="wizard-input flex-1 text-sm">
                        <button type="button" 
                                @click="searchLocation()" 
                                :disabled="isSearching"
                                class="px-4 py-2 bg-slate-800 text-white text-sm font-bold rounded-xl hover:bg-slate-700 transition-all disabled:opacity-50 flex items-center gap-2">
                            <span x-show="!isSearching">Cari</span>
                            <span x-show="isSearching">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </span>
                        </button>
                    </div>
                    <template x-if="searchError">
                        <p class="text-xs font-semibold text-red-500 mb-2" x-text="searchError"></p>
                    </template>

                    <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50">
                        <div id="wizard-map" class="w-full h-64 sm:h-80 relative" x-init="initMap()">
                            {{-- Leaflet will inject the map here --}}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Latitude</label>
                            <input type="number" step="0.00000001" x-model="form.latitude" placeholder="-6.12345678"
                                   class="wizard-input text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Longitude</label>
                            <input type="number" step="0.00000001" x-model="form.longitude" placeholder="106.12345678"
                                   class="wizard-input text-sm">
                        </div>
                    </div>
                    <input type="hidden" id="osm_url" x-model="osmUrl">
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="px-6 sm:px-8 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-secondary transition rounded-xl hover:bg-slate-100">
                    Batal
                </a>
                <button @click="submitStepOne()"
                        :disabled="isLoading"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/25 hover:bg-primary/90 hover:shadow-primary/40 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!isLoading">Lanjutkan</span>
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
         x-transition:enter-start="opacity-0 translate-x-8"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-8">

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            {{-- Card Header --}}
            <div class="px-6 sm:px-8 pt-6 sm:pt-8 pb-4 border-b border-slate-100">
                <h2 class="text-xl sm:text-2xl font-extrabold text-secondary font-display">Foto Properti</h2>
                <p class="text-sm text-slate-500 mt-1">Upload foto-foto terbaik properti Anda. Minimal 1 foto, maksimal 10 foto.</p>
            </div>

            <div class="px-6 sm:px-8 py-6 space-y-8">

                {{-- Photo Upload Zone --}}
                <div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="photo-preview-grid">
                        {{-- Photo previews --}}
                        <template x-for="(photo, index) in photoPreviews" :key="index">
                            <div class="relative group aspect-[4/3] rounded-xl overflow-hidden border-2 border-slate-200 bg-slate-50 shadow-sm">
                                <img :src="photo" class="w-full h-full object-cover" alt="Preview">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button @click="removePhoto(index)"
                                            class="w-9 h-9 rounded-full bg-white/90 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition-all shadow-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <template x-if="index === 0">
                                    <span class="absolute top-2 left-2 px-2 py-0.5 bg-primary text-white text-[10px] font-bold rounded-md uppercase tracking-wide">Utama</span>
                                </template>
                            </div>
                        </template>

                        {{-- Upload button --}}
                        <template x-if="photoPreviews.length < 10">
                            <label class="aspect-[4/3] rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/50 hover:border-primary hover:bg-primary/5 cursor-pointer flex flex-col items-center justify-center gap-1 transition-all duration-200 group">
                                <div class="w-10 h-10 rounded-full bg-primary/10 group-hover:bg-primary/20 flex items-center justify-center transition-colors">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                </div>
                                <span class="text-xs font-bold text-slate-400 group-hover:text-primary transition-colors">Tambah Foto</span>
                                <input type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden"
                                       @change="handlePhotoUpload($event)">
                            </label>
                        </template>
                    </div>
                    <template x-if="errors.photos"><p class="mt-2 text-xs font-semibold text-red-500" x-text="errors.photos"></p></template>
                </div>

                {{-- Certificate Section --}}
                <div class="pt-2">
                    <h3 class="text-lg font-extrabold text-secondary font-display">Sertifikat & Dokumen Legal</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Upload sertifikat properti. File diamankan menggunakan Signed URL.</p>

                    {{-- Certificate Upload --}}
                    <div class="mt-4">
                        <label class="block relative rounded-xl border-2 border-dashed transition-all duration-200 cursor-pointer group"
                               :class="certificateFile ? 'border-emerald-400 bg-emerald-50/50' : 'border-slate-300 hover:border-primary hover:bg-primary/5'">
                            <div class="flex items-center gap-4 p-4">
                                <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center"
                                     :class="certificateFile ? 'bg-emerald-100 text-emerald-600' : 'bg-primary/10 text-primary group-hover:bg-primary/20'">
                                    <template x-if="!certificateFile">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    </template>
                                    <template x-if="certificateFile">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </template>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <template x-if="!certificateFile">
                                        <div>
                                            <p class="text-sm font-bold text-secondary">Klik untuk upload sertifikat</p>
                                            <p class="text-xs text-slate-400 mt-0.5">Format: PDF, JPEG, PNG · Maks. 10MB</p>
                                        </div>
                                    </template>
                                    <template x-if="certificateFile">
                                        <div>
                                            <p class="text-sm font-bold text-emerald-700 truncate" x-text="certificateFile.name"></p>
                                            <p class="text-xs text-emerald-500 mt-0.5" x-text="formatFileSize(certificateFile.size)"></p>
                                        </div>
                                    </template>
                                </div>
                                <template x-if="certificateFile">
                                    <button @click.prevent="removeCertificate()" class="flex-shrink-0 w-8 h-8 rounded-lg bg-red-100 hover:bg-red-200 text-red-500 flex items-center justify-center transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </template>
                            </div>
                            <input type="file" accept=".pdf,image/jpeg,image/png" class="hidden"
                                   @change="handleCertificateUpload($event)">
                        </label>
                        <template x-if="errors.certificate_file"><p class="mt-2 text-xs font-semibold text-red-500" x-text="errors.certificate_file"></p></template>
                    </div>
                </div>

                {{-- Signed URL Info --}}
                <div class="rounded-xl bg-gradient-to-r from-primary/5 to-amber-50 border border-primary/15 p-4">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-primary/15 text-primary flex items-center justify-center mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-secondary">File sertifikat diamankan</p>
                            <p class="text-xs text-slate-500 mt-0.5">Akses ke file sertifikat menggunakan <span class="font-bold text-primary">Signed Temporary URL</span> dari Laravel yang berlaku selama 10 menit untuk keamanan dokumen sensitif.</p>
                        </div>
                    </div>
                </div>

                {{-- Agreement Checkbox --}}
                <label class="flex items-start gap-3 cursor-pointer select-none">
                    <input type="checkbox" x-model="agreedTerms"
                           class="mt-0.5 w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary/30 cursor-pointer">
                    <span class="text-sm text-slate-600 leading-relaxed">
                        Saya menyatakan bahwa data yang saya masukkan adalah <strong class="text-secondary">benar dan valid</strong>. Foto dan sertifikat yang diunggah sesuai dengan properti yang didaftarkan.
                    </span>
                </label>
            </div>

            {{-- Card Footer --}}
            <div class="px-6 sm:px-8 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                <button @click="currentStep = 1"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-secondary transition rounded-xl hover:bg-slate-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                    Kembali
                </button>
                <button @click="submitStepTwo()"
                        :disabled="isLoading || !agreedTerms"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/25 hover:bg-primary/90 hover:shadow-primary/40 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
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
         x-transition:enter-start="opacity-0 translate-x-8"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-8">

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            {{-- Card Header --}}
            <div class="px-6 sm:px-8 pt-6 sm:pt-8 pb-4 border-b border-slate-100">
                <h2 class="text-xl sm:text-2xl font-extrabold text-secondary font-display">Review Listing Anda</h2>
                <p class="text-sm text-slate-500 mt-1">Pastikan semua informasi sudah benar sebelum mengirimkan untuk verifikasi.</p>
            </div>

            <div class="px-6 sm:px-8 py-6 space-y-6">

                {{-- Detail Properti --}}
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3 bg-slate-50 border-b border-slate-100">
                        <h4 class="text-sm font-extrabold text-secondary">Detail Properti</h4>
                        <button @click="currentStep = 1" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            Edit
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Judul</span>
                                <p class="text-sm font-bold text-secondary mt-0.5" x-text="form.title || '-'"></p>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tipe</span>
                                <p class="text-sm font-bold text-secondary mt-0.5" x-text="form.type === 'residential' ? 'Residensial' : 'Tanah'"></p>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Harga</span>
                                <p class="text-sm font-bold text-primary mt-0.5" x-text="'Rp ' + (form.price || '0')"></p>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Sertifikat</span>
                                <p class="text-sm font-bold text-secondary mt-0.5" x-text="form.certificate_type || '-'"></p>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Luas Tanah</span>
                                <p class="text-sm font-bold text-secondary mt-0.5" x-text="(form.land_area_sqm || '0') + ' m²'"></p>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Luas Bangunan</span>
                                <p class="text-sm font-bold text-secondary mt-0.5" x-text="form.building_area_sqm ? form.building_area_sqm + ' m²' : '-'"></p>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Deskripsi</span>
                            <p class="text-sm text-slate-600 mt-0.5 leading-relaxed" x-text="form.description || '-'"></p>
                        </div>
                    </div>
                </div>

                {{-- Lokasi --}}
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3 bg-slate-50 border-b border-slate-100">
                        <h4 class="text-sm font-extrabold text-secondary">Lokasi</h4>
                        <button @click="currentStep = 1" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            Edit
                        </button>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                            <div class="col-span-2">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Alamat</span>
                                <p class="text-sm font-bold text-secondary mt-0.5" x-text="form.address || '-'"></p>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Provinsi</span>
                                <p class="text-sm font-bold text-secondary mt-0.5" x-text="form.province || '-'"></p>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kota/Kabupaten</span>
                                <p class="text-sm font-bold text-secondary mt-0.5" x-text="form.city || '-'"></p>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kecamatan</span>
                                <p class="text-sm font-bold text-secondary mt-0.5" x-text="form.district || '-'"></p>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Koordinat</span>
                                <p class="text-sm font-bold text-secondary mt-0.5"
                                   x-text="(form.latitude && form.longitude) ? form.latitude + ', ' + form.longitude : 'Belum ditentukan'"></p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Foto & Dokumen --}}
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3 bg-slate-50 border-b border-slate-100">
                        <h4 class="text-sm font-extrabold text-secondary">Foto & Dokumen</h4>
                        <button @click="currentStep = 2" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            Edit
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        {{-- Photo thumbnails --}}
                        <div>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Foto Properti</span>
                            <div class="flex gap-2 mt-2 overflow-x-auto pb-2">
                                <template x-for="(photo, index) in photoPreviews" :key="'review-'+index">
                                    <div class="flex-shrink-0 w-20 h-16 rounded-lg overflow-hidden border border-slate-200 shadow-sm">
                                        <img :src="photo" class="w-full h-full object-cover" alt="Preview">
                                    </div>
                                </template>
                            </div>
                            <p class="text-xs text-slate-400 mt-1" x-text="photoPreviews.length + ' foto diunggah'"></p>
                        </div>

                        {{-- Certificate status --}}
                        <div>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Sertifikat</span>
                            <div class="mt-2 flex items-center gap-2">
                                <template x-if="certificateFile">
                                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-50 border border-emerald-200">
                                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="text-sm font-bold text-emerald-700" x-text="certificateFile.name"></span>
                                        <span class="text-xs text-emerald-500 font-semibold">Signed URL</span>
                                    </div>
                                </template>
                                <template x-if="!certificateFile">
                                    <span class="text-sm text-slate-400">Belum diunggah</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status Badge --}}
                <div class="rounded-xl bg-gradient-to-r from-amber-50 to-primary/5 border border-amber-200/50 p-4">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.834-1.964-.834-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-secondary">Status akan berubah menjadi <span class="text-amber-600">Pending Verification</span></p>
                            <p class="text-xs text-slate-500 mt-0.5">Setelah submit, listing Anda akan ditinjau oleh tim verifikasi sebelum dipublikasikan di platform.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="px-6 sm:px-8 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                <button @click="currentStep = 2"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-secondary transition rounded-xl hover:bg-slate-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                    Kembali
                </button>
                <button @click="submitListing()"
                        :disabled="isLoading"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/25 hover:bg-primary/90 hover:shadow-primary/40 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
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
        <div class="bg-white rounded-2xl p-8 max-w-sm w-full mx-4 text-center shadow-2xl"
             x-transition:enter="transition ease-out duration-300 delay-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="w-20 h-20 mx-auto mb-5 rounded-full bg-emerald-100 flex items-center justify-center">
                <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-xl font-extrabold text-secondary font-display">Listing Berhasil Dikirim!</h3>
            <p class="text-sm text-slate-500 mt-2 leading-relaxed">Listing properti Anda sedang dalam proses verifikasi. Kami akan menghubungi Anda segera.</p>
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-2 mt-6 px-6 py-2.5 bg-primary text-white text-sm font-bold rounded-xl shadow-lg shadow-primary/25 hover:bg-primary/90 transition-all">
                Kembali ke Dashboard
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </div>
</div>

<style>
    .wizard-input {
        display: block;
        width: 100%;
        border-radius: 0.75rem;
        border: 1.5px solid #e2e8f0;
        background-color: #fff;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        font-family: 'Manrope', sans-serif;
        color: #2C3E50;
        outline: none;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.04);
    }
    .wizard-input::placeholder {
        color: #94a3b8;
    }
    .wizard-input:focus {
        border-color: #FC4907;
        box-shadow: 0 0 0 3px rgba(252, 73, 7, 0.12), 0 1px 2px 0 rgb(0 0 0 / 0.04);
    }
    .wizard-input:disabled {
        cursor: not-allowed;
        background-color: #f8fafc;
        color: #94a3b8;
    }
    select.wizard-input {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.25em 1.25em;
        padding-right: 2.5rem;
    }
</style>

<script>
function listingWizard() {
    return {
        currentStep: 1,
        propertyId: null,
        isLoading: false,
        showSuccess: false,
        agreedTerms: false,

        // State baru untuk fitur pencarian
        searchQuery: '',
        isSearching: false,
        searchError: '',
        osmUrl: '',

        form: {
            type: '',
            title: '',
            description: '',
            price: '',
            land_area_sqm: '',
            building_area_sqm: '',
            certificate_type: '',
            province: '',
            city: '',
            district: '',
            address: '',
            latitude: '',
            longitude: '',
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

        // Method Baru: Integrasi API Nominatim OSM
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

        initMap() {
            if (this.mapInstance) return;

            const defaultLat = -6.2088;
            const defaultLng = 106.8456;

            setTimeout(() => {
                this.mapInstance = L.map('wizard-map').setView([defaultLat, defaultLng], 12);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(this.mapInstance);

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
                this.mapInstance.setView(latlng, 15);
            }
        },

        createDraggableMarker(latlng) {
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