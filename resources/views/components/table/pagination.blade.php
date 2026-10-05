@props(['paginator'])

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination"
         {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row items-center justify-between gap-4']) }}>

        {{-- Results info --}}
        <p class="text-sm text-slate-500">
            Showing
            <span class="font-bold text-secondary">{{ $paginator->firstItem() }}</span>
            to
            <span class="font-bold text-secondary">{{ $paginator->lastItem() }}</span>
            of
            <span class="font-bold text-secondary">{{ $paginator->total() }}</span>
            results
        </p>

        <div class="flex items-center gap-1">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500
                          hover:text-primary hover:bg-primary-soft transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
            @endif

            {{-- Page numbers --}}
            @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span aria-current="page"
                          class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2 rounded-lg
                                 bg-primary text-white font-heading font-bold text-sm shadow-lg shadow-primary/25">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $url }}"
                       class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2 rounded-lg
                              text-sm font-semibold text-slate-600 hover:text-primary hover:bg-primary-soft transition">
                        {{ $page }}
                    </a>
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500
                          hover:text-primary hover:bg-primary-soft transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif