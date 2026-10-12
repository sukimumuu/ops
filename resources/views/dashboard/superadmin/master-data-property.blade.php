@extends('layouts.app')
@section('page-title', 'Master Data Property')
@section('content')
    <x-card-dashboard title="Daftar Properti" subtitle="Informasi tentang properti yang terdaftar">
        @include('dashboard.partials.property-review-feedback')
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
                    <x-table.data>
                        <a class="text-blue-800" href="{{ $property->url_maps }}" target="_blank">{{ $property->city }}</a>
                    </x-table.data>
                    <x-table.data>
                        @if ($property->status == 'pending_verification')
                            <span
                                class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Menunggu
                            </span>
                        @elseif($property->status == 'published')
                            <span
                                class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Terbit
                            </span>
                        @elseif($property->status == 'reserved')
                            <span
                                class="inline-flex rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Dipesan
                            </span>
                        @endif
                    </x-table.data>
                    <x-table.data>{{ $property->created_at->format('d M Y') }}</x-table.data>
                    <x-table.data>
                        <x-dashboard.property-approval-modal :property="$property" />
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
