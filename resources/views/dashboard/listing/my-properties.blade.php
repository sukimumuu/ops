@extends('layouts.app')
@section('page-title', 'Properti Saya')
@section('content')
    <x-card-dashboard>
        <div class="mx-auto w-full max-w-7xl font-sans text-slate-700">
            @if (session('success'))
                <div role="status"
                    class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <header class="mb-6 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <nav aria-label="Breadcrumb"
                        class="mb-3 flex flex-wrap items-center gap-2 font-sans text-xs font-medium text-slate-500 sm:text-sm">
                        <a href="{{ route('home') }}"
                            class="rounded transition hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                            Beranda
                        </a>
                        <span aria-hidden="true" class="text-slate-300">&gt;</span>
                        <a href="{{ route('seller.listing') }}"
                            class="rounded transition hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                            Dashboard Penjual
                        </a>
                        <span aria-hidden="true" class="text-slate-300">&gt;</span>
                        <span aria-current="page" class="font-semibold text-slate-600">Properti Saya</span>
                    </nav>

                    <h1 class="font-display text-3xl font-bold tracking-tight text-secondary sm:text-4xl">
                        Properti Saya
                    </h1>
                    <p class="mt-2 font-sans text-sm text-slate-500">
                        Informasi tentang properti yang Anda iklankan.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                    <p class="font-sans text-sm text-slate-500">
                        Anda memiliki {{ $totalProperties }} properti.
                    </p>

                    <form method="GET" action="{{ route('seller.my-properties') }}" class="flex items-center gap-2">
                        @foreach (request()->except(['sort', 'page']) as $queryKey => $queryValue)
                            @if (is_scalar($queryValue) && filled($queryValue))
                                <input type="hidden" name="{{ $queryKey }}" value="{{ $queryValue }}">
                            @endif
                        @endforeach

                        <label for="my-property-sort" class="shrink-0 font-sans text-sm text-slate-500">
                            Urutkan:
                        </label>
                        <select id="my-property-sort" name="sort" onchange="this.form.submit()"
                            class="rounded-lg border border-slate-200 bg-white py-2 pl-3 pr-8 font-sans text-sm font-semibold text-secondary shadow-sm transition hover:border-primary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                            @foreach ($sortOptions as $sortValue => $sortLabel)
                                <option value="{{ $sortValue }}" @selected($sort === $sortValue)>{{ $sortLabel }}
                                </option>
                            @endforeach
                        </select>
                        <noscript>
                            <button type="submit"
                                class="rounded-lg bg-primary px-3 py-2 font-sans text-sm font-semibold text-white transition hover:bg-primary/90">
                                Terapkan
                            </button>
                        </noscript>
                    </form>

                    <a href="{{ route('listing.create') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-3.5 py-2 font-heading text-xs font-semibold tracking-tight text-white shadow-lg shadow-primary/25 transition hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                        </svg>
                        Tambah Properti Baru
                    </a>
                </div>
            </header>

            <section aria-label="Filter properti"
                class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <x-search-filter placeholder="Cari judul, lokasi, atau alamat...">
                    <input type="hidden" name="sort" value="{{ $sort }}">

                    <div>
                        <label for="property-status-filter"
                            class="mb-1 block font-sans text-xs font-semibold text-slate-600">Status</label>
                        <select id="property-status-filter" name="status" onchange="this.form.submit()"
                            class="min-w-40 rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 font-sans text-sm text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="">Semua status</option>
                            @foreach ($statusOptions as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" @selected($status === $statusValue)>{{ $statusLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="property-category-filter"
                            class="mb-1 block font-sans text-xs font-semibold text-slate-600">Kategori</label>
                        <select id="property-category-filter" name="category" onchange="this.form.submit()"
                            class="min-w-36 rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 font-sans text-sm text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="">Semua kategori</option>
                            @foreach ($categoryOptions as $categoryValue => $categoryLabel)
                                <option value="{{ $categoryValue }}" @selected($category === $categoryValue)>{{ $categoryLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="property-type-filter"
                            class="mb-1 block font-sans text-xs font-semibold text-slate-600">Tipe properti</label>
                        <select id="property-type-filter" name="property_type" onchange="this.form.submit()"
                            class="min-w-36 rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 font-sans text-sm text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="">Semua tipe</option>
                            @foreach ($propertyTypeOptions as $typeValue => $typeLabel)
                                <option value="{{ $typeValue }}" @selected($propertyType === $typeValue)>{{ $typeLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </x-search-filter>
            </section>

            @if ($properties->isEmpty())
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white px-6 py-16 shadow-sm sm:px-10 sm:py-20">
                    <div class="mx-auto flex max-w-xl flex-col items-center text-center">
                        <div class="flex size-24 items-center justify-center rounded-full bg-primary/10 text-secondary"
                            aria-hidden="true">
                            <svg class="size-12" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 10.5 12 3l9 7.5M5 9.5V21h14V9.5" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 21v-5.5h5V21" />
                            </svg>
                        </div>

                        @if ($hasFilters)
                            <h2 class="mt-8 font-display text-2xl font-bold tracking-tight text-secondary sm:text-3xl">
                                Tidak ada properti yang cocok
                            </h2>
                            <p class="mt-3 max-w-lg font-sans text-sm leading-7 text-slate-500 sm:text-base">
                                Coba ubah kata kunci atau filter yang Anda gunakan untuk menemukan properti Anda.
                            </p>
                            <a href="{{ route('seller.my-properties') }}"
                                class="mt-8 inline-flex items-center justify-center rounded-lg border-2 border-primary px-6 py-3 font-sans text-sm font-semibold text-primary transition hover:bg-primary-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2">
                                Reset filter
                            </a>
                        @else
                            <h2 class="mt-8 font-display text-2xl font-bold tracking-tight text-secondary sm:text-3xl">
                                Belum ada properti
                            </h2>
                            <p class="mt-3 max-w-lg font-sans text-sm leading-7 text-slate-500 sm:text-base">
                                Mulai dengan menambahkan listing properti pertama Anda. Properti yang Anda tayangkan akan
                                muncul di sini.
                            </p>
                            <a href="{{ route('listing.create') }}"
                                class="mt-8 inline-flex items-center justify-center rounded-lg bg-primary px-6 py-3 font-sans text-sm font-semibold text-white shadow-lg shadow-primary/20 transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2">
                                Tambah Properti Baru
                            </a>
                        @endif
                    </div>
                </section>
            @else
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($properties as $property)
                        @php
                            $detailUrl = route('seller.my-properties.show', ['property' => $property->uuid]);
                        @endphp

                        <x-dashboard.property-card :property="$property" :detail-url="$detailUrl" :show-status="true">
                            <x-slot name="actions">
                                <a href="{{ $detailUrl }}"
                                    class="inline-flex items-center gap-1.5 rounded font-sans text-sm font-semibold text-primary transition hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:ring-offset-2">
                                    Lihat detail
                                    <span aria-hidden="true">&rarr;</span>
                                </a>
                            </x-slot>
                        </x-dashboard.property-card>
                    @endforeach
                </div>

                <x-table.pagination :paginator="$properties" class="mt-8" />
            @endif
        </div>
    </x-card-dashboard>
@endsection
