<aside x-cloak :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col
        transform bg-white text-black transition-transform duration-300 ease-in-out
        lg:translate-x-0 lg:hidden lg:bg-white lg:text-slate-700">

    <!-- Brand -->
    <div class="flex items-center justify-between h-16 px-6 border-b border-white/10 lg:border-slate-200">
        <a href="" class="flex items-center gap-2.5">
            <!-- Replace with your logo -->
            <img id="logo-black" src="{{ asset('assets/png/gry trnsprn.png') }}" alt="Mihom" class="h-8">
            <span class="font-heading text-xl font-bold text-white tracking-tight lg:text-black">
                Mihom
            </span>
        </a>

        <!-- Close button (mobile only) -->
        <button @click="sidebarOpen = false" class="lg:hidden text-white/70 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-1">
        @php($isSellerMode = request()->routeIs('seller.*', 'listing.*'))

        <p class="px-3 pb-2 text-[11px] font-bold uppercase trackeeeing-widest text-black/60 lg:text-black/40">
            Main Menu
        </p>

        @role('User')
            <div role="group" aria-label="Pilih mode" class="mb-4 grid grid-cols-2 rounded-lg bg-slate-100 p-1">
                <a href="{{ route('dashboard') }}"
                    @if (!$isSellerMode) aria-current="page" @endif
                    class="rounded-md px-3 py-2 text-center text-sm font-semibold transition {{ !$isSellerMode ? 'bg-white text-primary shadow-sm' : 'text-slate-600 hover:text-secondary' }}">
                    Pembeli
                </a>
                <a href="{{ route('seller.listing') }}" data-seller-feature
                    @if ($isSellerMode) aria-current="page" @endif
                    class="rounded-md px-3 py-2 text-center text-sm font-semibold transition {{ $isSellerMode ? 'bg-white text-primary shadow-sm' : 'text-slate-600 hover:text-secondary' }}">
                    Penjual
                </a>
            </div>
        @endrole

        <!-- Active link: bg-primary/10 text-primary -->
        @role('Superadmin')
            <a href=""
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold
                  {{ request()->routeIs('dashboard')
                      ? 'bg-primary text-white shadow-lg shadow-primary/25'
                      : 'text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 12l9-8 9 8M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10" />
                </svg>
                Dashboard Antrian
            </a>

            <a href="#"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Manajemen Pengguna
            </a>

            <a href="#"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Master Data Properti
            </a>

            <a href="#"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M18 17V9M13 17V5M8 17v-3" />
                </svg>
                Transaksi & Escrow
            </a>

            <a href="#"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Sistem & Konfigurasi
            </a>
            <a href="{{ route('superadmin.audit-log') }}"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold
                  {{ request()->routeIs('superadmin.audit-log')
                      ? 'bg-primary text-white shadow-lg shadow-primary/25'
                      : 'text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Audit Log
            </a>
        @endrole

        {{-- PPAT Menu --}}
        @role('PPAT')
            <a href="{{ route('land-deed.index') }}"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold 
        {{ request()->routeIs('land-deed.index') || request()->routeIs('dashboard')
            ? 'bg-primary text-white shadow-lg shadow-primary/25'
            : 'text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 12l9-8 9 8M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10" />
                </svg>
                Dashboard Antrian
            </a>

            <a href="{{ route('land-deed.client-properties') }}"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold 
        {{ request()->routeIs('land-deed.client-properties*')
            ? 'bg-primary text-white shadow-lg shadow-primary/25'
            : 'text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Properti Klien
            </a>

        @endrole

        {{-- Buyer Menu --}}
        @role('User')
            @unless ($isSellerMode)
            <a href="{{ route('dashboard') }}"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold
                  {{ request()->routeIs('dashboard')
                      ? 'bg-primary text-white shadow-lg shadow-primary/25'
                      : 'text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 12l9-8 9 8M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10" />
                </svg>
                Dashboard
            </a>

            <a href="{{ route('buyer.saved-properties') }}"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold
                  {{ request()->routeIs('buyer.saved-properties')
                      ? 'bg-primary text-white shadow-lg shadow-primary/25'
                      : 'text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                Properti Tersimpan
            </a>

            <a href="{{ route('buyer.survey-schedule') }}"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold
                  {{ request()->routeIs('buyer.survey-schedule*')
                      ? 'bg-primary text-white shadow-lg shadow-primary/25'
                      : 'text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Jadwal Survei Saya
            </a>

            <a href="{{ route('buyer.bought-properties') }}"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold
                  {{ request()->routeIs('buyer.bought-properties*')
                      ? 'bg-primary text-white shadow-lg shadow-primary/25'
                      : 'text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Properti Dibeli
            </a>

            @can('transactions.view_own_tracker')
            <a href="{{ route('buyer.transaction-escrow') }}"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M18 17V9M13 17V5M8 17v-3" />
                </svg>
                Transaksi & Escrow
            </a>
            @endcan

            <a href="{{ route('profile') }}"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold
                  {{ request()->routeIs('profile')
                      ? 'bg-primary text-white shadow-lg shadow-primary/25'
                      : 'text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Profil & Verifikasi (KYC)
            </a>
            @endunless
        @endrole

        {{-- Seller Menu --}}
        @role('User')
            @if ($isSellerMode)
            <a href="{{ route('seller.listing') }}" data-seller-feature
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold
                  {{ request()->routeIs('seller.listing')
                      ? 'bg-primary text-white shadow-lg shadow-primary/25'
                      : 'text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 12l9-8 9 8M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10" />
                </svg>
                Dashboard
            </a>

            <a href="{{ route('seller.my-properties') }}"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Properti Saya
            </a>

            <a href="{{ route('seller.request-survey') }}"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Permintaan Survei Masuk
            </a>

            @can('transactions.view_own_tracker')
            <a href="{{ route('seller.selling-transaction') }}"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M18 17V9M13 17V5M8 17v-3" />
                </svg>
                Transaksi Penjualan
            </a>
            @endcan

            <a href="{{ route('seller.disbursement-account') }}"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Rekening Pencairan
            </a>
            <a href="{{ route('profile') }}"
                class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold
                  {{ request()->routeIs('profile')
                      ? 'bg-primary text-white shadow-lg shadow-primary/25'
                      : 'text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Profil & Verifikasi (KYC)
            </a>
            @endif
        @endrole
    </nav>
</aside>
