@props(['property', 'compact' => false])

@php
    $area = $property->building_area_sqm ?? $property->land_area_sqm;
    $location = collect([$property->district, $property->city])->filter()->implode(', ');
@endphp

<a
    href="{{ route('property-details', ['uuid' => $property->uuid]) }}"
    class="group block rounded-2xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary"
>
    <article class="{{ $compact ? 'overflow-hidden rounded-2xl bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl' : 'overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-shadow duration-300 hover:shadow-xl' }}">
        <div class="relative {{ $compact ? 'h-48' : 'h-56' }} overflow-hidden bg-gray-100">
            <div class="flex h-full w-full items-center justify-center text-xs font-semibold text-gray-400" aria-hidden="true">
                Foto properti belum tersedia
            </div>
            @if (filled($property->image_url))
                <img
                    src="{{ $property->image_url }}"
                    alt="Foto properti: {{ $property->title }}"
                    class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    onerror="this.remove()"
                >
            @endif

            <span class="{{ $compact ? 'absolute left-3 top-3 rounded-full px-3 py-1 text-[11px] font-extrabold uppercase text-white ' . ($property->category === 'rent' ? 'bg-blue-500' : 'bg-primary') : 'absolute left-4 top-4 rounded-full bg-white px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-primary shadow-sm' }}">
                {{ $property->category === 'rent' ? 'Disewa' : 'Dijual' }}
            </span>

            @unless ($compact)
                <span class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-400 shadow-sm" aria-hidden="true">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                </span>
            @endunless
        </div>

        <div class="{{ $compact ? 'p-4' : 'p-5' }}">
            <p class="{{ $compact ? 'mb-1 text-lg' : 'mb-1 text-xl' }} font-extrabold text-primary">
                Rp {{ number_format((float) $property->price, 0, ',', '.') }}
            </p>
            <h3 class="{{ $compact ? 'mb-2 text-sm leading-5' : 'mb-1 text-lg' }} font-bold text-secondary">
                {{ $property->title }}
            </h3>
            <p class="{{ $compact ? 'mb-3' : 'mb-4' }} text-xs text-gray-500">
                {{ $location ?: 'Lokasi belum tersedia' }}
            </p>

            @if ($compact)
                <div class="flex gap-4 border-t border-gray-100 pt-3 text-xs font-semibold text-gray-500">
                    @if (filled($property->rooms))
                        <span>⌂ {{ $property->rooms }}</span>
                    @endif
                    @if (filled($area))
                        <span>▣ {{ number_format((float) $area, 0, ',', '.') }} m²</span>
                    @endif
                    @if (blank($property->rooms) && blank($area))
                        <span>Detail properti belum tersedia</span>
                    @endif
                </div>
            @else
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-600">
                    {{ $property->listing_specs ?: 'Detail properti belum tersedia' }}
                </div>
            @endif
        </div>
    </article>
</a>