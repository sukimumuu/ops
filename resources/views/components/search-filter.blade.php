@props(['placeholder' => 'Cari...'])

<form method="GET" action="{{ request()->url() }}"
    class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0 flex-1">
        <label for="list-search" class="sr-only">Cari data</label>
        <input id="list-search" type="search" name="search" value="{{ request('search') }}"
            placeholder="{{ $placeholder }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-secondary placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
    </div>
    <div class="flex flex-wrap items-end gap-3">
        {{ $slot }}
        <button type="submit"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
            Cari
        </button>
        <a href="{{ request()->url() }}"
            class="inline-flex items-center justify-center rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-secondary">
            Reset
        </a>
    </div>
</form>
