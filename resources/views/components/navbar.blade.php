<nav class="navbar fixed inset-x-0 top-0 z-50 transition-all duration-300" id="navbar">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2 no-underline">
            <img id="logo-black" src="{{ asset('assets/png/blck trnsprn.png') }}" alt="Mihom" class="hidden h-8">
            <img id="logo-white" src="{{ asset('assets/png/wht trnsprn.png') }}" alt="Mihom" class="h-8">
            <span class="nav-logo-text font-display text-[22px] font-extrabold tracking-tight text-white transition-colors">mihom</span>
        </a>
        <div class="hidden items-center gap-2 md:flex">
            <a href="{{ route('home') }}" class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/15">Beranda</a>
            <a href="{{ route('properti') }}" class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/15">Properti</a>
            <a href="#tentang" class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/15">Tentang Kami</a>
            <a href="#kontak" class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/15">Kontak</a>
        </div>
        <a href="{{ route('login') }}" class="hidden rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-[#df3f05] md:block">Daftar / Masuk</a>
        <button class="nav-hamburger flex h-10 w-10 flex-col items-center justify-center gap-[5px] rounded-lg border-0 bg-transparent p-0 md:hidden" id="hamburgerBtn" aria-label="Buka Menu" aria-expanded="false">
            <span class="hamburger-line h-0.5 w-[22px] rounded bg-white transition"></span><span class="hamburger-line h-0.5 w-[22px] rounded bg-white transition"></span><span class="hamburger-line h-0.5 w-[22px] rounded bg-white transition"></span>
        </button>
    </div>
</nav>