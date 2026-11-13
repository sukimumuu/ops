@extends('layouts.app')
@section('page-title', 'Edit properti')

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
    @include('listing-wizard.partials.styles')

    <div class="wizard-page" x-data="listingWizard()" x-cloak>
        <div class="wizard-page-header">
            <a href="{{ route('seller.my-properties.show', ['property' => $property->uuid]) }}" class="wizard-back-link">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
            <h1 class="wizard-page-title">Edit properti</h1>
            <x-property-status-badge :status="$property->status" />
        </div>

        @if ($errors->any())
            <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-bold">Perubahan belum dapat disimpan.</p>
                <ul class="mt-1 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('seller.my-properties.update', ['property' => $property->uuid]) }}"
            class="space-y-6">
            @csrf
            @method('PUT')

            <section class="wizard-card">
                <div class="wizard-card-body">
                    <div class="wizard-section">
                        <h2 class="wizard-section-title">Informasi properti</h2>
                        <p class="wizard-section-subtitle">Perbarui detail, spesifikasi, dan harga listing Anda.</p>

                        <div class="wizard-field mt-5">
                            <span class="wz-label">Tipe properti <span class="wz-required">*</span></span>
                            <div class="wizard-type-grid">
                                <label class="wizard-type-card"
                                    :class="{ 'selected': form.property_type === 'bangunan' }">
                                    <input type="radio" name="property_type" value="bangunan"
                                        x-model="form.property_type"
                                        @checked(old('property_type', $property->property_type) === 'bangunan')
                                        class="sr-only">
                                    <span class="wizard-type-check" x-show="form.property_type === 'bangunan'">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3"
                                            viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                    <span class="wizard-type-icon bangunan">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5"
                                            viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 21h18M5 21V7l8-4v18m6 0V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01" />
                                        </svg>
                                    </span>
                                    <span class="wizard-type-title">Bangunan</span>
                                    <span class="wizard-type-desc">Rumah, apartemen, ruko, dan lainnya</span>
                                </label>

                                <label class="wizard-type-card"
                                    :class="{ 'selected': form.property_type === 'tanah' }">
                                    <input type="radio" name="property_type" value="tanah" x-model="form.property_type"
                                        @checked(old('property_type', $property->property_type) === 'tanah')
                                        class="sr-only">
                                    <span class="wizard-type-check" x-show="form.property_type === 'tanah'">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3"
                                            viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                    <span class="wizard-type-icon tanah">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5"
                                            viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
                                        </svg>
                                    </span>
                                    <span class="wizard-type-title">Tanah</span>
                                    <span class="wizard-type-desc">Kavling, lahan, dan tanah komersial</span>
                                </label>
                            </div>
                            @error('property_type')
                                <p class="wz-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="wizard-form-grid grid-2 mt-5">
                            <template x-if="form.property_type === 'bangunan'">
                                <div class="wizard-field">
                                    <label for="building_type" class="wz-label">Jenis bangunan <span
                                            class="wz-required">*</span></label>
                                    <select id="building_type" name="building_type" x-model="form.building_type"
                                        class="wz-input">
                                        <option value="">Pilih jenis...</option>
                                        @foreach (['rumah' => 'Rumah', 'apartemen' => 'Apartemen', 'ruko' => 'Ruko', 'villa' => 'Villa', 'gudang' => 'Gudang', 'kantor' => 'Kantor'] as $type => $label)
                                            <option value="{{ $type }}"
                                                @selected(old('building_type', old('type', $property->type)) === $type)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                        @if ($property->property_type === 'bangunan' && !in_array($property->type, ['rumah', 'apartemen', 'ruko', 'villa', 'gudang', 'kantor'], true))
                                            <option value="{{ $property->type }}" selected>{{ ucfirst($property->type) }}
                                                (saat ini)</option>
                                        @endif
                                    </select>
                                    @error('type')
                                        <p class="wz-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </template>
                            <template x-if="form.property_type === 'tanah'">
                                <div class="wizard-field">
                                    <label for="land_type" class="wz-label">Jenis tanah <span
                                            class="wz-required">*</span></label>
                                    <select id="land_type" name="land_type" x-model="form.land_type" class="wz-input">
                                        <option value="">Pilih jenis...</option>
                                        @foreach (['kavling' => 'Kavling', 'sawah' => 'Sawah', 'kebun' => 'Kebun', 'komersial' => 'Komersial', 'industri' => 'Industri'] as $type => $label)
                                            <option value="{{ $type }}"
                                                @selected(old('land_type', old('type', $property->type)) === $type)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                        @if ($property->property_type === 'tanah' && !in_array($property->type, ['kavling', 'sawah', 'kebun', 'komersial', 'industri'], true))
                                            <option value="{{ $property->type }}" selected>{{ ucfirst($property->type) }}
                                                (saat ini)</option>
                                        @endif
                                    </select>
                                    @error('type')
                                        <p class="wz-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </template>
                            <div class="wizard-field">
                                <label for="transaction_type" class="wz-label">Tujuan transaksi <span
                                        class="wz-required">*</span></label>
                                <select id="transaction_type" name="transaction_type" x-model="form.transaction_type"
                                    class="wz-input">
                                    <option value="">Pilih tujuan...</option>
                                    <option value="dijual"
                                        @selected(old('transaction_type', old('category', $property->category) === 'sale' ? 'dijual' : 'disewakan') === 'dijual')>
                                        Dijual</option>
                                    <option value="disewakan"
                                        @selected(old('transaction_type', old('category', $property->category) === 'sale' ? 'dijual' : 'disewakan') === 'disewakan')>
                                        Disewakan</option>
                                </select>
                                @error('category')
                                    <p class="wz-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="wizard-field mt-5">
                            <label for="title" class="wz-label">Judul properti <span
                                    class="wz-required">*</span></label>
                            <input id="title" name="title" type="text" maxlength="255" required
                                x-model="form.title" value="{{ old('title', $property->title) }}"
                                placeholder="Contoh: Rumah modern di kawasan Bintaro" class="wz-input">
                            <p class="wz-helper">Gunakan judul yang singkat, jelas, dan menggambarkan properti Anda.</p>
                            @error('title')
                                <p class="wz-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="wizard-section mt-5">
                            <div class="wizard-field">
                                <label for="price" class="wz-label">Harga properti <span
                                        class="wz-required">*</span></label>
                                <div class="wz-price-input-group">
                                    <div class="wz-price-input-shell">
                                        <div class="wz-input-group">
                                            <span class="wz-input-prefix">Rp</span>
                                            <input id="price" name="price" type="text" required
                                                x-model="form.price"
                                                @input="form.price = formatCurrency($event.target.value)"
                                                value="{{ old('price', number_format((float) $property->price, 0, ',', '.')) }}"
                                                placeholder="1.850.000.000" class="wz-input has-prefix">
                                        </div>
                                    </div>
                                    <div class="wz-rent-period-shell" x-show="isRentTransaction()" x-transition>
                                        <select name="rent_period" x-model="form.rent_period"
                                            x-bind:disabled="!isRentTransaction()" class="wz-input">
                                            <option value="">Pilih periode</option>
                                            @foreach (['bulan' => 'Per bulan', 'tahun' => 'Per tahun', 'minggu' => 'Per minggu', 'hari' => 'Per hari'] as $period => $label)
                                                <option value="{{ $period }}"
                                                    @selected(old('rent_period', $property->rent_period) === $period)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <p class="wz-helper">Masukkan harga total properti, bukan harga per meter persegi.</p>
                                @error('price')
                                    <p class="wz-error">{{ $message }}</p>
                                @enderror
                                @error('rent_period')
                                    <p class="wz-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <label class="wz-checkbox-label mt-3">
                                <input type="hidden" name="negotiable" value="0">
                                <input type="checkbox" name="negotiable" value="1" x-model="form.negotiable"
                                    @checked(old('negotiable', $property->negotiable)) class="wz-checkbox">
                                <span>Harga dapat dinegosiasikan</span>
                            </label>
                        </div>

                        <div class="wizard-field mt-5">
                            <label for="description" class="wz-label">Deskripsi properti <span
                                    class="wz-required">*</span></label>
                            <textarea id="description" name="description" rows="5" maxlength="2000" required
                                x-model="form.description" placeholder="Jelaskan kondisi, keunggulan, dan akses di sekitar properti."
                                class="wz-input wz-textarea">{{ old('description', $property->description) }}</textarea>
                            <p class="wz-helper">Cantumkan kondisi, keunggulan, serta fasilitas di sekitar properti.</p>
                            @error('description')
                                <p class="wz-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="wizard-divider"></div>
                    <div class="wizard-section">
                        <h3 class="wizard-section-title">Spesifikasi properti</h3>
                        <p class="wizard-section-subtitle">Lengkapi ukuran, kondisi, dan karakteristik properti.</p>

                        <div class="wizard-form-grid grid-2 mt-5">
                            <div class="wizard-field">
                                <label for="land_area_sqm" class="wz-label">Luas tanah <span
                                        class="wz-required">*</span></label>
                                <div class="wz-input-group">
                                    <input id="land_area_sqm" name="land_area_sqm" type="number" min="1"
                                        step="any" required x-model="form.land_area_sqm"
                                        value="{{ old('land_area_sqm', $property->land_area_sqm) }}"
                                        placeholder="120" class="wz-input has-suffix">
                                    <span class="wz-input-suffix">m²</span>
                                </div>
                                @error('land_area_sqm')
                                    <p class="wz-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <template x-if="form.property_type === 'bangunan'">
                                <div class="wizard-field">
                                    <label for="building_area_sqm" class="wz-label">Luas bangunan <span
                                            class="wz-required">*</span></label>
                                    <div class="wz-input-group">
                                        <input id="building_area_sqm" name="building_area_sqm" type="number"
                                            min="0" step="any" x-model="form.building_area_sqm"
                                            value="{{ old('building_area_sqm', $property->building_area_sqm) }}"
                                            placeholder="150" class="wz-input has-suffix">
                                        <span class="wz-input-suffix">m²</span>
                                    </div>
                                    @error('building_area_sqm')
                                        <p class="wz-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </template>
                        </div>

                        <template x-if="form.property_type === 'bangunan'">
                            <div class="wizard-form-grid grid-3 mt-4">
                                <div class="wizard-field">
                                    <label for="bedrooms" class="wz-label">Kamar tidur</label>
                                    <input id="bedrooms" name="bedrooms" type="number" min="0"
                                        x-model="form.bedrooms" value="{{ old('bedrooms', old('rooms', $property->rooms)) }}"
                                        placeholder="3" class="wz-input">
                                    @error('rooms')
                                        <p class="wz-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="wizard-field">
                                    <label for="bathrooms" class="wz-label">Kamar mandi</label>
                                    <input id="bathrooms" name="bathrooms" type="number" min="0"
                                        x-model="form.bathrooms" value="{{ old('bathrooms', $property->bathrooms) }}"
                                        placeholder="2" class="wz-input">
                                    @error('bathrooms')
                                        <p class="wz-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="wizard-field">
                                    <label for="floors" class="wz-label">Jumlah lantai</label>
                                    <input id="floors" name="floors" type="number" min="1"
                                        x-model="form.floors" value="{{ old('floors', $property->floors) }}"
                                        placeholder="2" class="wz-input">
                                    @error('floors')
                                        <p class="wz-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="wizard-field">
                                    <label for="condition" class="wz-label">Kondisi bangunan</label>
                                    <select id="condition" name="condition" x-model="form.condition" class="wz-input">
                                        <option value="">Pilih kondisi...</option>
                                        @foreach (['baru' => 'Baru', 'siap_huni' => 'Siap huni', 'butuh_renovasi' => 'Butuh renovasi'] as $condition => $label)
                                            <option value="{{ $condition }}"
                                                @selected(old('condition', $property->condition) === $condition)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                        @if ($property->property_type === 'bangunan' && !in_array($property->condition, ['baru', 'siap_huni', 'butuh_renovasi'], true))
                                            <option value="{{ $property->condition }}" selected>{{ ucfirst(str_replace('_', ' ', $property->condition)) }}
                                                (saat ini)</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </template>

                        <template x-if="form.property_type === 'tanah'">
                            <div class="wizard-sections-wrapper">
                                <div class="wizard-form-grid grid-2 mt-4">
                                    <div class="wizard-field">
                                        <label for="front_width" class="wz-label">Lebar depan</label>
                                        <div class="wz-input-group">
                                            <input id="front_width" name="front_width" type="number" min="0"
                                                step="any" x-model="form.front_width"
                                                value="{{ old('front_width', $property->front_width) }}"
                                                placeholder="20" class="wz-input has-suffix">
                                            <span class="wz-input-suffix">m</span>
                                        </div>
                                        @error('front_width')
                                            <p class="wz-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="wizard-field">
                                        <label for="land_contour" class="wz-label">Kontur tanah</label>
                                        <select id="land_contour" name="land_contour" x-model="form.land_contour"
                                            class="wz-input">
                                            <option value="">Pilih kontur...</option>
                                            @foreach (['datar' => 'Datar', 'miring' => 'Miring', 'berbukit' => 'Berbukit'] as $contour => $label)
                                                <option value="{{ $contour }}"
                                                    @selected(old('land_contour', $property->land_contour) === $contour)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                            @if ($property->property_type === 'tanah' && filled($property->land_contour) && !in_array($property->land_contour, ['datar', 'miring', 'berbukit'], true))
                                                <option value="{{ $property->land_contour }}" selected>{{ ucfirst($property->land_contour) }}
                                                    (saat ini)</option>
                                            @endif
                                        </select>
                                        @error('land_contour')
                                            <p class="wz-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="wizard-form-grid grid-2 mt-4">
                                    <div class="wizard-field">
                                        <label for="road_access" class="wz-label">Akses jalan</label>
                                        <select id="road_access" name="road_access" x-model="form.road_access"
                                            class="wz-input">
                                            <option value="">Pilih akses...</option>
                                            @foreach (['jalan_utama' => 'Jalan utama', 'jalan_lingkungan' => 'Jalan lingkungan', 'gang' => 'Gang'] as $access => $label)
                                                <option value="{{ $access }}"
                                                    @selected(old('road_access', $property->road_access) === $access)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                            @if ($property->property_type === 'tanah' && filled($property->road_access) && !in_array($property->road_access, ['jalan_utama', 'jalan_lingkungan', 'gang'], true))
                                                <option value="{{ $property->road_access }}" selected>{{ ucfirst(str_replace('_', ' ', $property->road_access)) }}
                                                    (saat ini)</option>
                                            @endif
                                        </select>
                                        @error('road_access')
                                            <p class="wz-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="wizard-field">
                                        <label for="zone_type" class="wz-label">Peruntukan zona</label>
                                        <select id="zone_type" name="zone_type" x-model="form.zone_type" class="wz-input">
                                            <option value="">Pilih zona...</option>
                                            @foreach (['perumahan' => 'Perumahan', 'komersial' => 'Komersial', 'industri' => 'Industri', 'pertanian' => 'Pertanian', 'campuran' => 'Campuran'] as $zone => $label)
                                                <option value="{{ $zone }}"
                                                    @selected(old('zone_type', $property->zone_type) === $zone)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                            @if ($property->property_type === 'tanah' && filled($property->zone_type) && !in_array($property->zone_type, ['perumahan', 'komersial', 'industri', 'pertanian', 'campuran'], true))
                                                <option value="{{ $property->zone_type }}" selected>{{ ucfirst($property->zone_type) }}
                                                    (saat ini)</option>
                                            @endif
                                        </select>
                                        @error('zone_type')
                                            <p class="wz-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </section>

            <section class="wizard-card">
                <div class="wizard-card-body">
                    <div class="wizard-section">
                        <h2 class="wizard-section-title">Foto & dokumen legal</h2>
                        <p class="wizard-section-subtitle">Periksa file yang sudah tersimpan untuk properti ini.</p>

                        <div class="wizard-field mt-5">
                            <label for="certificate_type" class="wz-label">Jenis sertifikat <span
                                    class="wz-required">*</span></label>
                            <select id="certificate_type" name="certificate_type" required class="wz-input">
                                @foreach (\App\Http\Requests\StoreListingStepTwoRequest::CERTIFICATE_TYPES as $certificateType)
                                    <option value="{{ $certificateType }}"
                                        @selected(old('certificate_type', $property->certificate_type) === $certificateType)>
                                        {{ $certificateType }}
                                    </option>
                                @endforeach
                            </select>
                            @error('certificate_type')
                                <p class="wz-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="wizard-divider"></div>
                        <div class="wizard-form-grid grid-2">
                            <div class="wz-info-box">
                                <div class="wz-info-icon">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4 16l4-4a2 2 0 012.828 0L14 15m-1-1l1.172-1.172a2 2 0 012.828 0L20 16m-6-10h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="wz-info-title">Foto properti</p>
                                    <p class="wz-info-desc">{{ $property->photos->count() }} foto tersimpan</p>
                                </div>
                            </div>
                            <div class="wz-info-box">
                                <div class="wz-info-icon">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="wz-info-title">Sertifikat & dokumen</p>
                                    <p class="wz-info-desc">
                                        {{ $property->certificate->count() + $property->supportingDocuments->count() }}
                                        dokumen tersimpan
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if ($property->certificate->isNotEmpty() || $property->supportingDocuments->isNotEmpty())
                            <div class="wz-doc-list mt-4">
                                @foreach ($property->certificate as $certificate)
                                    <div class="wz-file-item">
                                        <span class="wz-file-icon" aria-hidden="true">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8m-5-5l5 5m-5-5v5h5M9 13h6M9 17h4" />
                                            </svg>
                                        </span>
                                        <span class="wz-file-info">
                                            <span class="wz-file-name">Sertifikat {{ $certificate->certificate_type ?? $loop->iteration }}</span>
                                            <span class="wz-file-meta">File tersimpan</span>
                                        </span>
                                    </div>
                                @endforeach
                                @foreach ($property->supportingDocuments as $document)
                                    <div class="wz-file-item">
                                        <span class="wz-file-icon" aria-hidden="true">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8m-5-5l5 5m-5-5v5h5M9 13h6M9 17h4" />
                                            </svg>
                                        </span>
                                        <span class="wz-file-info">
                                            <span class="wz-file-name">{{ strtoupper($document->type) }}</span>
                                            <span class="wz-file-meta">File tersimpan</span>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <p class="wz-helper mt-4">Foto dan dokumen yang sudah diunggah tetap tersimpan; penggantian atau
                            penghapusan file belum tersedia di halaman edit ini.</p>
                    </div>
                </div>
            </section>

            <section class="wizard-card">
                <div class="wizard-card-body">
                    <div class="wizard-section">
                        <h2 class="wizard-section-title">Lokasi properti</h2>
                        <p class="wizard-section-subtitle">Alamat yang akurat membantu calon pembeli menemukan properti Anda.</p>

                        @include('listing-wizard.partials.location-fields')

                        <div class="wizard-field mt-4">
                            <label for="address" class="wz-label">Alamat lengkap <span
                                    class="wz-required">*</span></label>
                            <input id="address" name="address" type="text" maxlength="500" required
                                x-model="form.address" value="{{ old('address', $property->address) }}"
                                placeholder="Jl. Bintaro Utama, Sektor 9, Blok H No. 12" class="wz-input">
                            <div class="wz-helper-icon mt-2">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Nomor rumah tidak ditampilkan ke publik. Lokasi akan ditampilkan secara
                                    perkiraan.</span>
                            </div>
                            @error('address')
                                <p class="wz-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="wizard-field mt-4">
                            <label class="wz-label">Titik lokasi di peta</label>
                            <div class="wz-map-search">
                                <input type="text" x-model="searchQuery" @keydown.enter.prevent="searchLocation()"
                                    placeholder="Cari alamat, nama jalan, atau daerah..."
                                    class="wz-input flex-1 text-sm">
                                <button type="button" @click="searchLocation()" :disabled="isSearching"
                                    class="wz-btn-search" aria-label="Cari lokasi">
                                    <span x-show="!isSearching">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </span>
                                    <span x-show="isSearching">
                                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                        </svg>
                                    </span>
                                </button>
                            </div>
                            <template x-if="searchError">
                                <p class="wz-error mt-2" x-text="searchError"></p>
                            </template>

                            <template x-if="form.property_type === 'bangunan'">
                                <div class="wz-map-container mt-3">
                                    <div id="wizard-map" class="wz-map" x-init="initMap()"></div>
                                </div>
                            </template>
                            <template x-if="form.property_type === 'tanah'">
                                <div class="wz-map-container mt-3">
                                    <div id="wizard-map-tanah" class="wz-map"
                                        x-init="initMap('wizard-map-tanah')"></div>
                                </div>
                            </template>

                            <div class="wizard-form-grid grid-2 mt-3">
                                <div class="wizard-field">
                                    <label for="latitude" class="wz-label-sm">Latitude</label>
                                    <input id="latitude" name="latitude" type="number" step="any"
                                        x-model="form.latitude" value="{{ old('latitude', $property->latitude) }}"
                                        placeholder="-6.12345678" class="wz-input text-sm">
                                    @error('latitude')
                                        <p class="wz-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="wizard-field">
                                    <label for="longitude" class="wz-label-sm">Longitude</label>
                                    <input id="longitude" name="longitude" type="number" step="any"
                                        x-model="form.longitude" value="{{ old('longitude', $property->longitude) }}"
                                        placeholder="106.12345678" class="wz-input text-sm">
                                    @error('longitude')
                                        <p class="wz-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <input type="hidden" name="url_maps" x-model="osmUrl"
                                value="{{ old('url_maps', $property->url_maps) }}">
                            @error('url_maps')
                                <p class="wz-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ route('seller.my-properties.show', ['property' => $property->uuid]) }}"
                    class="wz-btn-back">Batal</a>
                <button type="submit" class="wz-btn-primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M5 13l4 4L19 7M4 21h16a1 1 0 001-1V5a1 1 0 00-1-1H7L4 7v13a1 1 0 001 1z" />
                    </svg>
                    Simpan perubahan
                </button>
            </div>
        </form>
    </div>

    @include('listing-wizard.partials.scripts')
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endpush
@endsection
