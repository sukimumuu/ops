@props(['property'])

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

    @can('properties.approve_listing')
        <x-dashboard.ppat-profile-card :property="$property" />
    @endcan

    <div class="wz-review-block">
        <div class="wz-review-header">
            <h4>Foto & dokumen</h4>
        </div>
        <div class="wz-review-body">
            @can('documents.view_sensitive_pii')
                <div class="wz-review-item">
                    <span class="wz-review-label">Foto properti</span>
                    <div class="wz-review-photos">
                        @forelse ($property->photos as $photo)
                            <div class="wz-review-photo-thumb">
                                @if (filled($photo->file_path) && (Storage::disk('public')->exists($photo->file_path) || Storage::disk('local')->exists($photo->file_path)))
                                    <img src="{{ route('property-review.file', $photo) }}"
                                        alt="Foto {{ $property->title }}"
                                        onerror="this.hidden = true; this.nextElementSibling.hidden = false;">
                                    <span class="wz-review-empty" hidden>Foto tidak tersedia</span>
                                @else
                                    <span class="wz-review-empty">Foto tidak tersedia</span>
                                @endif
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
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            @if (filled($certificate->file_path) && Storage::disk('local')->exists($certificate->file_path))
                                <a href="{{ route('property-review.file', $certificate) }}" target="_blank"
                                    rel="noopener noreferrer">Lihat sertifikat</a>
                            @else
                                <span>Sertifikat tidak tersedia</span>
                            @endif
                        </div>
                    @empty
                        <span class="wz-review-empty">Belum diunggah</span>
                    @endforelse
                </div>
            @endcan
                <p class="wz-review-photo-count">{{ $property->photos->count() }} foto diunggah</p>
            </div>
            <div class="wz-review-item mt-3">
                <span class="wz-review-label">Sertifikat</span>
                @forelse ($property->certificate as $certificate)
                    <div class="wz-review-cert-badge">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        @if (filled($certificate->file_path) && Storage::disk('local')->exists($certificate->file_path))
                            <a href="{{ route('property-review.file', $certificate) }}" target="_blank"
                                rel="noopener noreferrer">Lihat sertifikat{{ $certificate->certificate_type ? ' ('.$certificate->certificate_type.')' : '' }}</a>
                        @else
                            <span>Sertifikat tidak tersedia</span>
                        @endif
                    </div>
                @empty
                    <span class="wz-review-empty">Belum diunggah</span>
                @endforelse
            </div>
            @if ($property->supportingDocuments->isNotEmpty())
                <div class="wz-review-item mt-3">
                    <span class="wz-review-label">Dokumen pendukung</span>
                    @foreach ($property->supportingDocuments as $document)
                        <div class="wz-review-cert-badge">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8m-5-5l5 5m-5-5v5h5" />
                            </svg>
                            @if (filled($document->file_path) && Storage::disk('local')->exists($document->file_path))
                                <a href="{{ route('property-review.file', $document) }}" target="_blank"
                                    rel="noopener noreferrer">
                                    {{ $document->type === 'imb' ? 'Lihat IMB / PBG' : 'Lihat SPPT & STTS PBB' }}
                                </a>
                            @else
                                <span>Dokumen tidak tersedia</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <x-slot name="footer">
        <div class="flex justify-end gap-3">
            @if ($property->status === 'pending_verification')
                <x-button variant="secondary" @click="open = false">Batal</x-button>
                @can('properties.reject_listing')
                    <form method="POST" action="{{ route('property-review.status', $property) }}"
                        onsubmit="this.querySelector('button[type=submit]').disabled = true;">
                        @csrf
                        <input type="hidden" name="status" value="rejected">
                        <x-button type="submit" variant="danger">Tolak</x-button>
                    </form>
                @endcan
                @can('properties.approve_listing')
                    <form method="POST" action="{{ route('property-review.status', $property) }}"
                        onsubmit="this.querySelector('button[type=submit]').disabled = true;">
                        @csrf
                        <input type="hidden" name="status" value="published">
                        <x-button type="submit" variant="approve">Setujui</x-button>
                    </form>
                @endcan
            @else
                <form method="POST" action="{{ route('property-review.status', $property) }}"
                    onsubmit="this.querySelector('button[type=submit]').disabled = true;">
                    @csrf
                    <input type="hidden" name="status" value="archived">
                    <x-button type="submit" variant="secondary">Arsipkan</x-button>
                </form>
            @endif
        </div>
    </x-slot>
</x-drawer>

@once
    <style>
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
@endonce
