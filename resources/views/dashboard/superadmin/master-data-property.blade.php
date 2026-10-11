@extends('layouts.app')
@section('page-title', 'Master Data Property')
@section('content')
    <x-card-dashboard title="Daftar Properti" subtitle="Informasi tentang properti yang terdaftar">
        <div class="mb-4">
            <x-search-filter placeholder="Cari judul, lokasi, atau penjual...">
                <div>
                    <label for="property-type-filter" class="mb-1 block text-xs font-semibold text-slate-600">Tipe
                        properti</label>
                    <select id="property-type-filter" name="property_type"
                        class="min-w-36 rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="">Semua tipe</option>
                        <option value="bangunan" @selected(request('property_type') === 'bangunan')>Bangunan</option>
                        <option value="tanah" @selected(request('property_type') === 'tanah')>Tanah</option>
                    </select>
                </div>
                <div>
                    <label for="property-category-filter"
                        class="mb-1 block text-xs font-semibold text-slate-600">Kategori</label>
                    <select id="property-category-filter" name="category"
                        class="min-w-36 rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="">Semua kategori</option>
                        <option value="sale" @selected(request('category') === 'sale')>Dijual</option>
                        <option value="rent" @selected(request('category') === 'rent')>Disewakan</option>
                    </select>
                </div>
            </x-search-filter>
        </div>
        <x-table>
            <x-slot name="head">
                <x-table.cell>Properti</x-table.cell>
                <x-table.cell>Penjual</x-table.cell>
                <x-table.cell>Lokasi</x-table.cell>
                <x-table.cell>Status</x-table.cell>
                <x-table.cell>Diajukan</x-table.cell>
                <x-table.cell>Aksi</x-table.cell>
            </x-slot>
            @forelse ($pendingProperties as $property)
                <x-table.row striped>
                    <x-table.data>
                        <p class="max-w-56 truncate font-semibold text-secondary">{{ $property->title }}</p>
                    </x-table.data>
                    <x-table.data>{{ $property->seller->name }}</x-table.data>
                    <x-table.data>{{ $property->city }}</x-table.data>
                    <x-table.data>
                        <span
                            class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Menunggu</span>
                    </x-table.data>
                    <x-table.data>{{ $property->created_at->format('d M Y') }}</x-table.data>
                    <x-table.data>
                        <x-drawer title="Persetujuan Properti" width="lg:w-2/5">
                            <x-slot name="trigger">
                                <x-button variant="secondary" size="sm">Detail</x-button>
                            </x-slot>
                            <div class="wz-review-block">
                                <div class="wz-review-header">
                                    <h4>Detail properti</h4>
                                </div>
                                <div class="wz-review-body">
                                    <div class="wz-review-grid">
                                        <div class="wz-review-item">
                                            <span class="wz-review-label">Tipe properti</span>
                                            <span
                                                class="wz-review-value">{{ $property->property_type === 'bangunan' ? 'Bangunan' : 'Tanah' }}</span>
                                        </div>
                                        <div class="wz-review-item">
                                            <span class="wz-review-label">Judul</span>
                                            <span class="wz-review-value">{{ $property->title }}</span>
                                        </div>
                                        <div class="wz-review-item">
                                            <span class="wz-review-label">Harga</span>
                                            <span class="wz-review-value wz-price">Rp
                                                {{ number_format((float) $property->price, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="wz-review-item">
                                            <span class="wz-review-label">Sertifikat</span>
                                            <span class="wz-review-value">{{ $property->certificate_type ?: '-' }}</span>
                                        </div>
                                        <div class="wz-review-item">
                                            <span class="wz-review-label">Luas tanah</span>
                                            <span class="wz-review-value">{{ $property->land_area_sqm }} m²</span>
                                        </div>
                                        @if ($property->property_type === 'bangunan')
                                            <div class="wz-review-item">
                                                <span class="wz-review-label">Luas bangunan</span>
                                                <span
                                                    class="wz-review-value">{{ $property->building_area_sqm ? $property->building_area_sqm . ' m²' : '-' }}</span>
                                            </div>
                                            <div class="wz-review-item">
                                                <span class="wz-review-label">Kamar tidur / mandi</span>
                                                <span class="wz-review-value">{{ $property->rooms ?: '-' }} KT /
                                                    {{ $property->bathrooms ?: '-' }} KM</span>
                                            </div>
                                        @else
                                            <div class="wz-review-item">
                                                <span class="wz-review-label">Kontur tanah</span>
                                                <span class="wz-review-value">{{ $property->land_contour ?: '-' }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="wz-review-item mt-3" style="grid-column: 1/-1;">
                                        <span class="wz-review-label">Deskripsi</span>
                                        <p class="wz-review-desc">{{ $property->description ?: '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Review: Lokasi --}}
                            <div class="wz-review-block">
                                <div class="wz-review-header">
                                    <h4>Lokasi</h4>
                                </div>
                                <div class="wz-review-body">
                                    <div class="wz-review-grid">
                                        <div class="wz-review-item" style="grid-column: 1/-1;">
                                            <span class="wz-review-label">Alamat</span>
                                            <span class="wz-review-value">{{ $property->address }}</span>
                                        </div>
                                        <div class="wz-review-item">
                                            <span class="wz-review-label">Provinsi</span>
                                            <span class="wz-review-value">{{ $property->province }}</span>
                                        </div>
                                        <div class="wz-review-item">
                                            <span class="wz-review-label">Kota/Kabupaten</span>
                                            <span class="wz-review-value">{{ $property->city }}</span>
                                        </div>
                                        <div class="wz-review-item">
                                            <span class="wz-review-label">Kecamatan</span>
                                            <span class="wz-review-value">{{ $property->district }}</span>
                                        </div>
                                        <div class="wz-review-item">
                                            <span class="wz-review-label">Koordinat</span>
                                            <span
                                                class="wz-review-value">{{ $property->latitude && $property->longitude ? $property->latitude . ', ' . $property->longitude : 'Belum ditentukan' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Review: Foto & Dokumen --}}
                            <div class="wz-review-block">
                                <div class="wz-review-header">
                                    <h4>Foto & dokumen</h4>
                                </div>
                                <div class="wz-review-body">
                                    <div class="wz-review-item">
                                        <span class="wz-review-label">Foto properti</span>
                                        <div class="wz-review-photos">
                                            @forelse ($property->photos as $photo)
                                                <div class="wz-review-photo-thumb">
                                                    <img src="{{ Storage::url($photo->file_path) }}"
                                                        alt="Foto {{ $property->title }}">
                                                </div>
                                            @empty
                                                <span class="wz-review-empty">Belum ada foto</span>
                                            @endforelse
                                        </div>
                                        <p class="wz-review-photo-count">{{ $property->photos->count() }} foto diunggah</p>
                                    </div>
                                    <div class="wz-review-item mt-3">
                                        <span class="wz-review-label">Sertifikat</span>
                                        @forelse ($property->certificate as $certificate)
                                            <div class="wz-review-cert-badge">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <a href="{{ Storage::url($certificate->file_path) }}" target="_blank"
                                                    rel="noopener noreferrer">Lihat sertifikat</a>
                                            </div>
                                        @empty
                                            <span class="wz-review-empty">Belum diunggah</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            <x-slot name="footer">
                                <div class="flex justify-end gap-3">
                                    <x-button variant="secondary" @click="open = false">Batal</x-button>
                                    <x-button variant="danger" @click="open = false">Tolak</x-button>
                                    <x-button variant="approve" @click="open = false">Setujui</x-button>
                                </div>
                            </x-slot>
                        </x-drawer>
                    </x-table.data>
                </x-table.row>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">Tidak ada properti yang
                        menunggu verifikasi.</td>
                </tr>
            @endforelse
        </x-table>
        <x-table.pagination :paginator="$pendingProperties" class="mt-4" />
    </x-card-dashboard>
@endsection
<style>
    /* ===== REVIEW BLOCKS ===== */
    .wz-review-block {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 16px;
    }

    .wz-review-block:last-of-type {
        margin-bottom: 0;
    }

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

    .wz-review-edit:hover {
        opacity: 0.7;
        text-decoration: underline;
    }

    .wz-review-body {
        padding: 16px 20px;
    }

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

    .wz-review-value.wz-price {
        color: #FC4907;
    }

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

    .wz-review-photo-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .wz-review-photo-count {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 6px;
    }

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

    .wz-review-cert-badge svg {
        color: #22c55e;
    }

    .wz-review-empty {
        font-size: 13px;
        color: #94a3b8;
        margin-top: 4px;
        display: block;
    }
</style>
