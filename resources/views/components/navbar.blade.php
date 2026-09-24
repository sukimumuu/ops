<nav class="navbar fixed inset-x-0 top-0 z-50 transition-all duration-300" id="navbar">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
        <a href="{{ route('welcome') }}" class="flex items-center gap-2 no-underline">
            <img id="logo-black" src="<?php echo asset('assets/png/blck trnsprn.png'); ?>" alt="Mihom" class="hidden h-8 [.scrolled_&]:block">
            <img id="logo-white" src="<?php echo asset('assets/png/wht trnsprn.png'); ?>" alt="Mihom" class="h-8 [.scrolled_&]:hidden">
            
            <span class="nav-logo-text font-display text-[22px] font-extrabold tracking-tight text-white transition-colors [.scrolled_&]:text-gray-900">mihom</span>
        </a>

        <div class="hidden items-center gap-2 md:flex">
            <a href="{{ route('welcome') }}" class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/15 [.scrolled_&]:text-gray-700 [.scrolled_&]:hover:bg-gray-100">Beranda</a>
            <a href="#properti" class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/15 [.scrolled_&]:text-gray-700 [.scrolled_&]:hover:bg-gray-100">Properti</a>
            <a href="#tentang" class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/15 [.scrolled_&]:text-gray-700 [.scrolled_&]:hover:bg-gray-100">Tentang Kami</a>
            <a href="#kontak" class="nav-link rounded-lg px-4 py-2 text-sm font-semibold text-white/90 transition hover:bg-white/15 [.scrolled_&]:text-gray-700 [.scrolled_&]:hover:bg-gray-100">Kontak</a>
        </div>

        <a href="{{ route('login') }}" class="hidden rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-[#df3f05] md:block">Daftar / Masuk</a>
            <button class="nav-hamburger flex h-10 w-10 flex-col items-center justify-center gap-[5px] rounded-lg border-0 bg-transparent p-0 md:hidden" id="hamburgerBtn" aria-label="Buka Menu" aria-expanded="false">
                <span class="hamburger-line h-0.5 w-[22px] rounded bg-white transition"></span><span class="hamburger-line h-0.5 w-[22px] rounded bg-white transition"></span><span class="hamburger-line h-0.5 w-[22px] rounded bg-white transition"></span>
            </button>

        <button class="nav-hamburger flex h-10 w-10 flex-col items-center justify-center gap-[5px] rounded-lg border-0 bg-transparent p-0 md:hidden" id="hamburgerBtn" aria-label="Buka Menu" aria-expanded="false">
            <span class="hamburger-line h-0.5 w-[22px] rounded bg-white transition [.scrolled_&]:bg-gray-900"></span>
            <span class="hamburger-line h-0.5 w-[22px] rounded bg-white transition [.scrolled_&]:bg-gray-900"></span>
            <span class="hamburger-line h-0.5 w-[22px] rounded bg-white transition [.scrolled_&]:bg-gray-900"></span>
        </button>
    </div>
</nav>

<script>
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 20) {
            navbar.classList.add('scrolled', 'bg-white', 'shadow-md');
        } else {
            navbar.classList.remove('scrolled', 'bg-white', 'shadow-md');
        }
    });
</script>