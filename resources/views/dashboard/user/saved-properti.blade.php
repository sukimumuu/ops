@extends('layouts.app')

@section('page-title', 'Properti Tersimpan')

@section('content')
    <div class="mx-auto w-full max-w-7xl font-sans text-slate-700">
        @if (session('success'))
            <div role="status"
                class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @forelse ($savedProperties as $property)
            @if ($loop->first)
                <header class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <nav aria-label="Breadcrumb"
                            class="mb-3 flex flex-wrap items-center gap-2 font-sans text-xs font-medium text-slate-500 sm:text-sm">
                            <a href="{{ route('home') }}"
                                class="rounded transition hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                                Beranda
                            </a>
                            <span aria-hidden="true" class="text-slate-300">&gt;</span>
                            <span aria-current="page" class="font-semibold text-slate-600">Properti Tersimpan</span>
                        </nav>

                        <h1 class="font-display text-3xl font-bold tracking-tight text-secondary sm:text-4xl">
                            Properti Tersimpan
                        </h1>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                        <p class="font-sans text-sm text-slate-500">
                            Anda memiliki {{ $savedProperties->count() }} properti yang disimpan.
                        </p>

                        <form method="GET" action="{{ route('buyer.saved-properties') }}"
                            class="flex items-center gap-2">
                            @foreach (request()->except(['sort', 'page']) as $queryKey => $queryValue)
                                @if (is_scalar($queryValue) && filled($queryValue))
                                    <input type="hidden" name="{{ $queryKey }}" value="{{ $queryValue }}">
                                @endif
                            @endforeach

                            <label for="saved-property-sort" class="shrink-0 font-sans text-sm text-slate-500">
                                Urutkan:
                            </label>
                            <select id="saved-property-sort" name="sort" onchange="this.form.submit()"
                                class="rounded-lg border border-slate-200 bg-white py-2 pl-3 pr-8 font-sans text-sm font-semibold text-secondary shadow-sm transition hover:border-primary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                                <option value="newest" @selected($sort === 'newest')>Terbaru</option>
                                <option value="oldest" @selected($sort === 'oldest')>Terlama</option>
                            </select>
                            <noscript>
                                <button type="submit"
                                    class="rounded-lg bg-primary px-3 py-2 font-sans text-sm font-semibold text-white transition hover:bg-primary/90">
                                    Terapkan
                                </button>
                            </noscript>
                        </form>
                    </div>
                </header>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @endif

            <x-dashboard.property-card
                :property="$property"
                :detail-url="route('property-details', ['uuid' => $property->uuid])"
                removable
                :remove-url="route('buyer.saved-properties.destroy', ['uuid' => $property->uuid])"
            />

            @if ($loop->last)
                </div>

                <div class="mt-8 flex justify-end">
                    <a href="{{ route('properties') }}"
                        class="inline-flex items-center gap-2 rounded font-sans text-sm font-semibold text-primary transition hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:ring-offset-2">
                        Jelajahi properti lainnya
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            @endif
        @empty
            <section
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white px-6 py-16 shadow-sm sm:px-10 sm:py-20">
                <div class="mx-auto flex max-w-xl flex-col items-center text-center">
                    <div class="relative flex size-24 items-center justify-center rounded-full bg-primary/10 text-secondary"
                        aria-hidden="true">
                        <svg class="size-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5M5 9.5V21h14V9.5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 21v-5.5h5V21" />
                        </svg>
                        <span
                            class="absolute -bottom-1 -right-1 flex size-9 items-center justify-center rounded-full bg-white shadow-md ring-1 ring-slate-100">
                            <svg class="size-5 text-primary" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                            </svg>
                        </span>
                    </div>

                    <h1 class="mt-8 font-display text-2xl font-bold tracking-tight text-secondary sm:text-3xl">
                        Belum ada properti yang disimpan
                    </h1>

                    <p class="mt-3 max-w-lg font-sans text-sm leading-7 text-slate-500 sm:text-base">
                        Temukan rumah yang Anda suka, lalu klik ikon hati untuk menyimpannya. Favorit Anda akan muncul
                        di sini.
                    </p>

                    <a href="{{ route('properties') }}"
                        class="mt-8 inline-flex items-center justify-center rounded-lg bg-primary px-6 py-3 font-sans text-sm font-semibold text-white shadow-lg shadow-primary/20 transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2">
                        Cari Properti Sekarang
                    </a>
                </div>
            </section>
        @endforelse
    </div>
@endsection
