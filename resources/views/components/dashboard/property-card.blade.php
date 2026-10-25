@props([
    'property',
    'detailUrl' => null,
    'showStatus' => false,
    'removable' => false,
    'removeUrl' => null,
])

@php
    $price = (float) $property->price;
    $area = (float) $property->building_area_sqm > 0
        ? $property->building_area_sqm
        : $property->land_area_sqm;
    $location = collect([$property->district, $property->city])->filter()->implode(', ');
    $location = $location !== '' ? $location : $property->address;
    $hasRooms = filled($property->rooms) && (float) $property->rooms > 0;
    $hasBathrooms = filled($property->bathrooms) && (float) $property->bathrooms > 0;
    $hasArea = filled($area) && (float) $area > 0;
    $rentPeriod = $property->category === 'rent'
        ? match ($property->rent_period) {
            'bulan' => '/ bulan',
            'tahun' => '/ tahun',
            'minggu' => '/ minggu',
            'hari' => '/ hari',
            default => null,
        }
        : null;
@endphp

<article
    class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:shadow-lg">
    <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
        <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-slate-400" aria-hidden="true">
            <svg class="size-9" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5M5 9.5V21h5v-6h4v6h5V9.5" />
            </svg>
            <span class="font-sans text-xs font-medium">Foto belum tersedia</span>
        </div>

        @if ($detailUrl)
            <a href="{{ $detailUrl }}" aria-label="Lihat detail {{ $property->title }}"
                class="absolute inset-0 block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary">
                @if (filled($property->image_url))
                    <img src="{{ $property->image_url }}" alt="Foto properti: {{ $property->title }}"
                        loading="lazy"
                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        onerror="this.remove()">
                @endif
            </a>
        @elseif (filled($property->image_url))
            <img src="{{ $property->image_url }}" alt="Foto properti: {{ $property->title }}" loading="lazy"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                onerror="this.remove()">
        @endif

        <span
            class="pointer-events-none absolute left-3 top-3 z-10 inline-flex items-center rounded-full bg-white px-3 py-1 font-sans text-[11px] font-bold tracking-wide text-secondary shadow-sm">
            {{ $property->category === 'rent' ? 'Disewakan' : 'Dijual' }}
        </span>

        @if ($removable && $removeUrl)
            <form method="POST" action="{{ $removeUrl }}" class="absolute right-3 top-3 z-10">
                @csrf
                @method('DELETE')
                <button type="submit" title="Hapus dari properti tersimpan"
                    aria-label="Hapus {{ $property->title }} dari properti tersimpan"
                    class="flex size-10 items-center justify-center rounded-full bg-white text-primary shadow-sm transition hover:bg-orange-50 hover:text-primary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2">
                    <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path
                            d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                    </svg>
                </button>
            </form>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-5">
        @if ($showStatus)
            <div class="mb-3">
                <x-property-status-badge :status="$property->status" />
            </div>
        @endif

        <div class="mb-1 flex flex-wrap items-baseline gap-x-1.5 gap-y-0.5">
            @if ($price > 0)
                <p class="font-display text-xl font-bold tracking-tight text-secondary">
                    Rp {{ number_format($price, 0, ',', '.') }}
                </p>
                @if ($rentPeriod)
                    <span class="font-sans text-xs font-medium text-slate-500">{{ $rentPeriod }}</span>
                @endif
            @else
                <p class="font-display text-lg font-bold text-secondary">Harga belum tersedia</p>
            @endif
        </div>

        <h2 class="font-display text-base font-bold leading-6 text-secondary">
            @if ($detailUrl)
                <a href="{{ $detailUrl }}"
                    class="line-clamp-2 rounded transition hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                    {{ $property->title }}
                </a>
            @else
                <span class="line-clamp-2">{{ $property->title }}</span>
            @endif
        </h2>

        @if (filled($location))
            <p class="mt-2 flex items-start gap-1.5 font-sans text-sm text-slate-500">
                <svg class="mt-0.5 size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                    viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11Z" />
                    <circle cx="12" cy="10" r="2.5" />
                </svg>
                <span class="line-clamp-2">{{ $location }}</span>
            </p>
        @endif

        @if ($hasRooms || $hasBathrooms || $hasArea)
            <div
                class="mt-auto flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-slate-100 pt-4 font-sans text-xs font-semibold text-slate-600">
                @if ($hasRooms)
                    <span class="inline-flex items-center gap-1.5" title="Kamar tidur">
                        <svg class="size-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 11h18M5 11V7a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v4m0 0V8a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v3M3 11v7m18-7v7M3 18h18" />
                        </svg>
                        {{ $property->rooms }} KT
                    </span>
                @endif

                @if ($hasBathrooms)
                    <span class="inline-flex items-center gap-1.5" title="Kamar mandi">
                        <svg class="size-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 12h16v1.5A3.5 3.5 0 0 1 16.5 17h-9A3.5 3.5 0 0 1 4 13.5V12Zm2 0V6.5A2.5 2.5 0 0 1 8.5 4c1.2 0 2.2.84 2.46 2M7 17l-1 3m11-3 1 3" />
                        </svg>
                        {{ $property->bathrooms }} KM
                    </span>
                @endif

                @if ($hasArea)
                    <span class="inline-flex items-center gap-1.5" title="Luas properti">
                        <svg class="size-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 9V4h5M20 9V4h-5M4 15v5h5m11-5v5h-5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 15 6-6" />
                        </svg>
                        {{ number_format((float) $area, 0, ',', '.') }} m²
                    </span>
                @endif
            </div>
        @else
            <div class="mt-auto border-t border-slate-100 pt-4 font-sans text-xs text-slate-400">
                Detail properti belum tersedia
            </div>
        @endif

        @isset($actions)
            <div class="mt-4 flex items-center justify-end border-t border-slate-100 pt-4">
                {{ $actions }}
            </div>
        @endisset
    </div>
</article>
