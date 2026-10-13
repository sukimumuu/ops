@props(['property', 'ppatUsers'])

@php
    $propertyDistrict = mb_strtolower(trim((string) $property->district));
    $orderedPpatUsers = $ppatUsers
        ->sortBy(
            fn($ppat) => filled($propertyDistrict) &&
            mb_strtolower(trim((string) $ppat->district)) === $propertyDistrict
                ? 0
                : 1,
        )
        ->values();
@endphp

<section class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <header class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3">
        <div>
            <h4 class="text-sm font-bold text-secondary">PPAT terkait</h4>
            <p class="mt-0.5 text-xs text-slate-500">
                Prioritas kecamatan: {{ $property->district ?: 'Belum ditentukan' }}
            </p>
        </div>
        <span class="shrink-0 rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">
            {{ $orderedPpatUsers->count() }}
        </span>
    </header>

    <div class="max-h-64 divide-y divide-slate-100 overflow-y-auto">
        @forelse ($orderedPpatUsers as $ppat)
            @php
                $matchesDistrict =
                    filled($propertyDistrict) && mb_strtolower(trim((string) $ppat->district)) === $propertyDistrict;
                $location = collect([$ppat->district, $ppat->city, $ppat->province])
                    ->filter()
                    ->implode(', ');
            @endphp
            <article class="flex items-start gap-3 px-4 py-3">
                <span
                    class="flex size-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-secondary">
                    {{ mb_strtoupper(mb_substr($ppat->name, 0, 1)) }}
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h5 class="truncate text-sm font-semibold text-secondary">{{ $ppat->name }}</h5>
                        <span @class([
                            'rounded-full px-2 py-0.5 text-[10px] font-semibold',
                            'bg-emerald-50 text-emerald-700' => $matchesDistrict,
                            'bg-slate-100 text-slate-600' => !$matchesDistrict,
                        ])>
                            {{ $matchesDistrict ? 'Kecamatan sesuai' : 'Kecamatan lain' }}
                        </span>
                    </div>
                    <p class="mt-0.5 truncate text-xs text-slate-500">
                        {{ $location ?: 'Wilayah belum dilengkapi' }}
                    </p>
                    @if (filled($ppat->phone))
                        <a href="tel:+{{ preg_replace('/\D+/', '', $ppat->phone) }}"
                            class="mt-1 inline-block text-xs font-semibold text-primary hover:underline">
                            {{ $ppat->phone }}
                        </a>
                    @endif
                </div>
            </article>
        @empty
            <p class="px-4 py-5 text-sm text-slate-500">Belum ada pengguna dengan role PPAT.</p>
        @endforelse
    </div>
</section>
