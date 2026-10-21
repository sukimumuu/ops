{{-- Compact AI action shown beside the description label. --}}
<button
    type="button"
    @click="generateDescription()"
    :disabled="isGeneratingDescription"
    aria-label="✨ Buat deskripsi iklan dengan AI"
    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-primary bg-white px-2.5 py-1.5 text-[11px] font-semibold text-primary transition hover:bg-primary/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60 sm:text-xs"
>
    <template x-if="!isGeneratingDescription">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l1.7 4.6L18 9.3l-4.3 1.7L12 15.6l-1.7-4.6L6 9.3l4.3-1.7L12 3zM18.5 15l.9 2.4 2.1.9-2.1.9-.9 2.4-.9-2.4-2.1-.9 2.1-.9.9-2.4z" />
        </svg>
    </template>
    <template x-if="isGeneratingDescription">
        <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
    </template>
    <span x-show="!isGeneratingDescription">Buat deskripsi iklan dengan AI</span>
    <span x-show="isGeneratingDescription">Membuat deskripsi...</span>
</button>
