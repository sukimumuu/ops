<nav class="navbar fixed inset-x-0 top-0 z-50 transition-all duration-350" id="navbar">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2 no-underline">
            <img id="logo-black" src="{{ asset('assets/png/gry trnsprn.png') }}" alt="Mihom"
                class="hidden h-8 transition duration-100 ">
            <img id="logo-white" src="{{ asset('assets/png/wht trnsprn.png') }}" alt="Mihom"
                class="h-8 transition duration-100 ">
            <span
                class="nav-logo-text font-display text-[22px] font-extrabold tracking-tight text-white transition duration-100 -colors">mihom</span>
        </a>
        <div class="hidden items-center gap-2 md:flex">
            <a href="{{ route('home') }}"
                class="nav-link {{ request()->routeIs('home') ? 'active' : '' }} rounded-lg px-4 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/15">Beranda</a>
            <a href="{{ route('properties') }}"
                class="nav-link {{ request()->routeIs('properties') ? 'active' : '' }} rounded-lg px-4 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/15">Properti</a>
            <a href="{{ route('tax-calculator') }}"
                class="nav-link {{ request()->routeIs('tax-calculator') ? 'active' : '' }} rounded-lg px-4 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/15">Kalkulator
                Pajak</a>
            <a href="{{ route('about') }}"
                class="nav-link {{ request()->routeIs('about') ? 'active' : '' }} rounded-lg px-4 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/15">Tentang
                Kami</a>
            <a href="{{ route('contact') }}"
                class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }} rounded-lg px-4 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/15">Kontak</a>
        </div>
        @guest
            <a href="{{ route('login') }}"
                class="hidden rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-[#df3f05] md:block">Daftar
                / Masuk</a>
        @endguest
        @auth
            <span class="flex gap-2 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ffffff" class="size-6">
                    <path fill-rule="evenodd"
                        d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653Zm-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438ZM15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"
                        clip-rule="evenodd" />
                </svg>
                <a href="{{ route('dashboard') }}" class="text-sm font-bold md:block">
                    {{ Auth::user()->name }}
                </a>
            </span>
        @endauth
        <button
            class="nav-hamburger flex h-10 w-10 flex-col items-center justify-center gap-[5px] rounded-lg border-0 bg-transparent p-0 md:hidden"
            id="hamburgerBtn" aria-label="Buka Menu" aria-expanded="false">
            <span class="hamburger-line h-0.5 w-[22px] rounded bg-white transition"></span><span
                class="hamburger-line h-0.5 w-[22px] rounded bg-white transition"></span><span
                class="hamburger-line h-0.5 w-[22px] rounded bg-white transition"></span>
        </button>
    </div>
</nav>
