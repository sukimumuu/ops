<div
    data-chatbot
    data-endpoint="{{ route('chatbot.reply') }}"
    data-csrf-token="{{ csrf_token() }}"
    data-conversation-id="{{ session('mihom_chat_conversation_id', '') }}"
    class="font-sans"
>
    <button
        type="button"
        data-chatbot-launcher
        aria-label="Buka asisten Mihom"
        aria-expanded="false"
        aria-controls="mihom-chat-panel"
        class="fixed bottom-4 right-4 z-[70] flex h-14 w-14 items-center justify-center rounded-full bg-primary text-white shadow-xl shadow-primary/30 transition hover:scale-105 hover:bg-[#df3f05] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-primary/30 active:scale-95"
    >
        <svg aria-hidden="true" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5m-8 6 2.1-3.15A8 8 0 1 1 20 12a7.96 7.96 0 0 1-1.1 4.05L21 20l-4.1-1.35A8 8 0 0 1 5 12" />
        </svg>
    </button>

    <section
        id="mihom-chat-panel"
        data-chatbot-panel
        role="region"
        aria-label="Asisten properti Mihom"
        hidden
        class="hidden fixed bottom-4 right-4 z-[70] flex h-[min(680px,calc(100dvh-2rem))] w-[min(400px,calc(100vw-2rem))] flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white text-secondary shadow-2xl shadow-secondary/20"
    >
        <header class="flex shrink-0 items-center justify-between gap-3 bg-secondary px-5 py-4 text-white">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/15">
                    <svg aria-hidden="true" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5m-8 6 2.1-3.15A8 8 0 1 1 20 12a7.96 7.96 0 0 1-1.1 4.05L21 20l-4.1-1.35A8 8 0 0 1 5 12" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <h2 class="truncate font-display text-base font-bold">Mihom AI</h2>
                    <p class="text-xs text-white/75">Asisten properti Anda</p>
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-1">
                <button
                    type="button"
                    data-chatbot-minimize
                    aria-label="Minimalkan chat"
                    class="flex h-9 w-9 items-center justify-center rounded-full text-white/80 transition hover:bg-white/10 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                >
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                    </svg>
                </button>
                <button
                    type="button"
                    data-chatbot-close
                    aria-label="Tutup chat"
                    class="flex h-9 w-9 items-center justify-center rounded-full text-white/80 transition hover:bg-white/10 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                >
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>
        </header>

        <div class="flex min-h-0 flex-1 flex-col overflow-y-auto">
            <div data-chatbot-welcome class="flex flex-1 flex-col gap-5 p-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary">Halo, selamat datang</p>
                    <h3 class="mt-2 font-display text-2xl font-bold leading-tight text-secondary">Bagaimana saya bisa membantu Anda hari ini?</h3>
                </div>

                <button
                    type="button"
                    data-chatbot-question="Bantu saya menemukan properti yang cocok"
                    class="group flex items-center justify-between gap-3 rounded-2xl bg-primary p-4 text-left text-white transition hover:bg-[#df3f05] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-primary/25 active:scale-[.99]"
                >
                    <span class="flex flex-col gap-1">
                        <span class="font-display text-base font-bold">Temukan properti impian</span>
                        <span class="text-xs leading-5 text-white/85">Ceritakan kebutuhan dan anggaran Anda.</span>
                    </span>
                    <svg aria-hidden="true" class="h-5 w-5 shrink-0 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7" />
                    </svg>
                </button>

                <div class="flex items-center gap-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                    <span class="h-px flex-1 bg-slate-200"></span>
                    <span>atau jelajahi</span>
                    <span class="h-px flex-1 bg-slate-200"></span>
                </div>

                <div class="flex flex-col gap-2">
                    <button type="button" data-chatbot-question="Cari rumah di Banyumas di bawah 500 juta" class="group flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 text-left text-sm text-secondary transition hover:border-primary/50 hover:bg-orange-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                        <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-primary" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z" />
                        </svg>
                        <span class="flex-1">Rumah sesuai anggaran</span>
                        <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-1 group-hover:text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7" />
                        </svg>
                    </button>
                    <button type="button" data-chatbot-question="Tolong jelaskan hal yang perlu diperiksa sebelum membeli properti" class="group flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 text-left text-sm text-secondary transition hover:border-primary/50 hover:bg-orange-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                        <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-primary" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h6l6 6v10a2 2 0 0 1-2 2Z" />
                        </svg>
                        <span class="flex-1">Tips membeli properti</span>
                        <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-1 group-hover:text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7" />
                        </svg>
                    </button>
                    <button type="button" data-chatbot-question="Apa perbedaan rumah dan apartemen untuk investasi?" class="group flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 text-left text-sm text-secondary transition hover:border-primary/50 hover:bg-orange-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                        <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-primary" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M15 9h.01M9 13h.01M15 13h.01M10 21v-4h4v4" />
                        </svg>
                        <span class="flex-1">Bandingkan tipe properti</span>
                        <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-1 group-hover:text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>

            <div id="chat-messages" data-chatbot-messages aria-live="polite" aria-relevant="additions text" class="hidden flex flex-col gap-3 p-5"></div>
            <div data-chatbot-properties class="flex flex-col gap-2 px-5"></div>
            <p data-chatbot-status role="status" hidden class="px-5 pb-3 text-xs text-slate-500"></p>
        </div>

        <form data-chatbot-form class="shrink-0 border-t border-slate-200 bg-white p-4">
            <div class="flex items-end gap-2 rounded-2xl border border-slate-300 p-2 transition focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/15">
                <label class="sr-only" for="chat-input">Tulis pesan</label>
                <textarea
                    id="chat-input"
                    data-chatbot-input
                    rows="1"
                    maxlength="2000"
                    placeholder="Tanyakan apa saja..."
                    class="max-h-24 min-h-10 flex-1 resize-y border-0 bg-transparent px-2 py-2 text-sm text-secondary outline-none placeholder:text-slate-400 focus:ring-0"
                ></textarea>
                <button
                    id="send-btn"
                    type="submit"
                    data-chatbot-send
                    aria-label="Kirim pesan"
                    disabled
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary text-white transition hover:bg-[#df3f05] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m-7 7 7-7 7 7" />
                    </svg>
                </button>
            </div>
            <p class="mt-2 px-1 text-center text-[10px] leading-4 text-slate-400">Jawaban AI dapat keliru. Pastikan kembali informasi properti sebelum mengambil keputusan.</p>
        </form>
    </section>
</div>
