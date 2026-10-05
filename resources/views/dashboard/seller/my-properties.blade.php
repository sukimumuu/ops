@extends('layouts.app')
@section('page-title', 'Properti Saya')
@section('content')
    <x-card-dashboard title="Properti Saya"
            subtitle="Informasi tentang properti yang saya iklankan">
        <x-slot name="actions">
            <x-button-dashboard variant="primary" size="sm">
                <a href="{{ route('listing.create') }}">Tambah Properti Baru</a> 
            </x-button-dashboard>
        </x-slot>
        <div class="space-y-4">
            <x-table>
                <x-slot name="head">
                    <x-table.cell>Nama Listing</x-table.cell>
                    <x-table.cell>Tipe</x-table.cell>
                    <x-table.cell>Harga</x-table.cell>
                    <x-table.cell>Status</x-table.cell>
                    <x-table.cell>Lokasi</x-table.cell>
                    <x-table.cell class="text-right">Actions</x-table.cell>
                </x-slot>

                @forelse($properties as $property)
                    <x-table.row striped>
                        <x-table.data>
                            <p class="font-bold">{{ $property->title }}</p>
                        </x-table.data>
                        <x-table.data>
                            <p class="font-bold">
                                @if ($property->type === 'residential')
                                    Rumah
                                @elseif ($property->type === 'land')
                                    Tanah
                                @else
                                    {{ ucfirst($property->type) }}
                                @endif
                            </p>
                        </x-table.data>
                        <x-table.data>
                            <p class="font-bold">{{ 'Rp.    '.number_format($property->price, 0, ',', '.') }}</p>
                        </x-table.data>
                        <x-table.data>
                            <span @class([
                                'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold',
                                'bg-slate-100 text-slate-700 border border-slate-200' => $property->status === 'draft',
                                'bg-amber-50 text-amber-700 border border-amber-200'   => $property->status === 'pending_verification',
                                'bg-emerald-50 text-emerald-700 border border-emerald-200' => $property->status === 'published',
                                'bg-sky-50 text-sky-700 border border-sky-200'         => $property->status === 'reserved',
                                'bg-purple-50 text-purple-700 border border-purple-200' => $property->status === 'sold',
                                'bg-rose-50 text-rose-700 border border-rose-200'     => $property->status === 'archived',
                            ])>
                                @if ($property->status === 'draft')
                                    Draft
                                @elseif ($property->status === 'pending_verification')
                                    Menunggu Verifikasi
                                @elseif ($property->status === 'published')
                                    Terbit
                                @elseif ($property->status === 'reserved')
                                    Dipesan
                                @elseif ($property->status === 'sold')
                                    Terjual
                                @elseif ($property->status === 'archived')
                                    Diarsipkan
                                @else
                                    {{ ucfirst($property->status) }}
                                @endif
                            </span>
                        </x-table.data>
                        <x-table.data class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="text-red-600 size-6">
                                <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                            </svg>
                            <a class="text-blue-600" href="{{ $property->url_maps }}" target="_blank">{{ 'Lokasi '.$property->title }}</a>
                        </x-table.data>
                        <x-table.data class="text-right">
                            <a href=""
                               class="text-sm font-bold text-primary hover:underline">Edit</a>
                        </x-table.data>
                    </x-table.row>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <p class="font-heading font-bold text-slate-400">No properties found</p>
                            <p class="mt-1 text-sm text-slate-400">Create your first property to get started.</p>
                        </td>
                    </tr>
                @endforelse
            </x-table>

            {{-- Pagination --}}
            <x-table.pagination :paginator="$properties" />
        </div>
        
    </x-card-dashboard>
@endsection