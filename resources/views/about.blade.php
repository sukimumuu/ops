<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Kenali Mihom, platform properti yang membantu Anda menemukan tempat yang tepat untuk memulai cerita baru.">
    <link rel="icon" href="{{ asset('assets/png/favicongray.ico') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" href="{{ asset('assets/png/faviconwhite.ico') }}" media="(prefers-color-scheme: dark)">
    <title>Tentang Kami - Mihom</title>
    @vite(['resources/css/app.css', 'resources/css/welcome.css', 'resources/js/app.js'])
</head>

<body class="m-0 bg-white font-sans text-secondary antialiased">
    <x-navbar />

    <div class="mobile-nav fixed inset-x-0 top-16 z-40 hidden flex-col gap-1 border-b border-gray-100 bg-white/95 p-4 shadow-xl backdrop-blur-md md:hidden"
        id="mobileNav">
        <a href="{{ route('home') }}" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Beranda</a>
        <a href="{{ route('properties') }}" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Properti</a>
        <a href="{{ route('tax-calculator') }}" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Kalkulator Pajak</a>
        <a href="{{ route('about') }}" class="rounded-lg px-4 py-3 text-sm font-semibold text-primary hover:bg-[#fff0eb]" onclick="closeMobileNav()">Tentang Kami</a>
        <a href="{{ route('contact') }}" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Kontak</a>
        @guest
            <a href="{{ route('login') }}" class="mt-2 rounded-lg bg-primary px-5 py-3 text-center text-sm font-bold text-white">Daftar / Masuk</a>
        @endguest
        @auth
            <a href="{{ route('dashboard') }}" class="mt-2 rounded-lg bg-primary px-5 py-3 text-center text-sm font-bold text-white">{{ Auth::user()->name }}</a>
        @endauth
    </div>

    <main>
        <header class="relative flex min-h-[390px] items-center overflow-hidden bg-secondary pt-16 sm:min-h-[430px]">
            <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1800&q=85&auto=format&fit=crop"
                alt="Rumah modern dengan taman dan kolam renang" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-[linear-gradient(110deg,rgb(31_45_58/88%),rgb(44_62_80/68%)_58%,rgb(44_62_80/38%))]"></div>
            <div class="relative z-10 mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <nav aria-label="Breadcrumb" class="mb-5 text-sm text-white/70">
                    <a href="{{ route('home') }}" class="transition hover:text-white">Beranda</a>
                    <span class="mx-2" aria-hidden="true">›</span>
                    <span aria-current="page" class="text-white">Tentang Kami</span>
                </nav>
                <h1 class="max-w-2xl font-display text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Lebih Dekat dengan Mihom
                </h1>
                <p class="mt-5 max-w-xl text-base leading-7 text-white/80">
                    Mengenal semangat kami untuk membantu Anda menemukan tempat yang tepat untuk memulai cerita baru.
                </p>
            </div>
        </header>

        <section class="px-4 py-16 sm:px-6 lg:py-20">
            <div class="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-2 lg:gap-16">
                <figure>
                    <img src="https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1100&q=85&auto=format&fit=crop"
                        alt="Ruang keluarga nyaman dengan pemandangan taman" class="h-72 w-full rounded-2xl object-cover sm:h-[390px]">
                    <figcaption class="mt-3 text-xs text-slate-400">Ilustrasi hunian — ruang untuk memulai cerita baru.</figcaption>
                </figure>
                <div>
                    <p class="mb-3 text-xs font-extrabold uppercase tracking-[0.18em] text-primary">Cerita Kami</p>
                    <h2 class="font-display text-3xl font-extrabold leading-tight text-secondary sm:text-4xl">
                        Setiap Rumah Punya Cerita. Begitu Juga Kami.
                    </h2>
                    <p class="mt-5 text-sm leading-6 text-slate-500">
                        Rumah bukan sekadar bangunan. Ia adalah tempat bertumbuh, berbagi, dan merencanakan masa depan. Gagasan inilah yang menjadi titik awal cerita Mihom.
                    </p>
                    <p class="mt-4 text-sm leading-6 text-slate-500">
                        Kami ingin menghadirkan pengalaman mencari, membeli, dan menyewa properti yang lebih mudah dipahami. Dengan informasi yang jelas dan percakapan yang terbuka, setiap langkah terasa lebih dekat dengan kebutuhan Anda.
                    </p>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="border-l-2 border-primary pl-4">
                            <h3 class="font-display text-lg font-bold text-secondary">Visi Kami</h3>
                            <p class="mt-1 text-sm leading-5 text-slate-500">Menjadi tempat yang nyaman bagi setiap perjalanan menemukan properti.</p>
                        </div>
                        <div class="border-l-2 border-primary pl-4">
                            <h3 class="font-display text-lg font-bold text-secondary">Misi Kami</h3>
                            <p class="mt-1 text-sm leading-5 text-slate-500">Menghubungkan kebutuhan, pilihan properti, dan pendampingan dalam satu pengalaman.</p>
                        </div>
                    </div>
                    <p class="mt-6 flex items-start gap-2 text-xs leading-5 text-slate-400">
                        <svg class="mt-0.5 size-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"></circle><path stroke-linecap="round" d="M12 11v5m0-8h.01"></path>
                        </svg>
                        Cerita, visi, dan misi di atas merupakan contoh profil perusahaan.
                    </p>
                </div>
            </div>
        </section>

        <section class="bg-slate-50 px-4 py-16 sm:px-6 lg:py-20">
            <div class="mx-auto max-w-7xl">
                <p class="mb-3 text-xs font-extrabold uppercase tracking-[0.18em] text-primary">Nilai Kami</p>
                <h2 class="font-display text-3xl font-extrabold text-secondary sm:text-4xl">Yang Menjadi Pegangan Kami</h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">Contoh nilai yang membentuk pengalaman properti yang lebih manusiawi.</p>
                <div class="mt-8 grid gap-5 md:grid-cols-3">
                    @foreach ([
                        ['Transparansi', 'Informasi yang mudah dipahami, komunikasi yang terbuka, dan kejelasan di setiap tahap perjalanan properti.', 'M5 12.5 9.2 17 19 7'],
                        ['Berorientasi pada Anda', 'Setiap kebutuhan berbeda. Kami mengutamakan mendengar agar pilihan yang Anda jelajahi terasa relevan.', 'M12 21s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 5.6-7 10-7 10Z'],
                        ['Tumbuh Bersama', 'Hubungan yang baik tidak berhenti pada pencarian. Kami ingin menjadi teman diskusi untuk langkah berikutnya.', 'M12 21v-8m0 0c-5 0-7-3-7-7 4 0 7 2 7 7Zm0-3c0-5 3-8 7-8 0 4-2 8-7 8Z'],
                    ] as [$title, $description, $iconPath])
                        <article class="rounded-2xl border border-slate-200 bg-white p-6 transition hover:-translate-y-1 hover:shadow-lg">
                            <span class="flex size-12 items-center justify-center rounded-xl bg-[#fff0eb] text-primary">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"></path>
                                </svg>
                            </span>
                            <h3 class="mt-5 font-display text-lg font-bold text-secondary">{{ $title }}</h3>
                            <p class="mt-3 text-sm leading-6 text-slate-500">{{ $description }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="px-4 py-16 sm:px-6 lg:py-20">
            <div class="mx-auto max-w-7xl">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="mb-3 text-xs font-extrabold uppercase tracking-[0.18em] text-primary">Di Balik Mihom</p>
                    <h2 class="font-display text-3xl font-extrabold text-secondary sm:text-4xl">Orang-orang yang Siap Mendengarkan</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-500">Kenali peran yang mendampingi perjalanan properti Anda, dari pertanyaan pertama hingga langkah berikutnya.</p>
                </div>
                <div class="mt-9 grid gap-5 md:grid-cols-3">
                    @foreach ([
                        ['Nadia Putri', 'Konsultan Properti', 'Membantu memahami kebutuhan dan mengeksplorasi pilihan hunian.', 'photo-1580489944761-15a19d654956'],
                        ['Raka Pratama', 'Relasi Agen & Mitra', 'Menghubungkan percakapan antara pencari properti dan mitra.', 'photo-1500648767791-00dcc994a43e'],
                        ['Alya Rahma', 'Layanan Pelanggan', 'Mendengarkan pertanyaan dan mengarahkan langkah berikutnya.', 'photo-1587614382346-4ec70e388b28'],
                    ] as [$name, $role, $description, $photo])
                        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                            <img src="https://images.unsplash.com/{{ $photo }}?w=850&q=85&auto=format&fit=crop"
                                alt="Ilustrasi {{ $name }}" class="h-56 w-full object-cover">
                            <div class="p-5">
                                <h3 class="font-display text-lg font-bold text-secondary">{{ $name }}</h3>
                                <p class="mt-1 text-sm font-semibold text-primary">{{ $role }}</p>
                                <p class="mt-2 text-sm leading-6 text-slate-500">{{ $description }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
                <p class="mt-5 flex items-start gap-2 text-xs leading-5 text-slate-400">
                    <svg class="mt-0.5 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"></circle><path stroke-linecap="round" d="M12 11v5m0-8h.01"></path>
                    </svg>
                    Contoh profil tim. Nama, peran, dan foto bersifat ilustratif, bukan identitas tim sebenarnya.
                </p>
            </div>
        </section>

        <section class="bg-secondary px-4 py-14 text-white sm:px-6 lg:py-16">
            <div class="mx-auto max-w-7xl">
                <div class="text-center">
                    <p class="mb-3 text-xs font-extrabold uppercase tracking-[0.18em] text-primary">Langkah yang Lebih Yakin</p>
                    <h2 class="font-display text-3xl font-extrabold sm:text-4xl">Kepercayaan Dimulai dari Kejelasan</h2>
                </div>
                <div class="mt-10 grid gap-8 md:grid-cols-3">
                    @foreach ([
                        ['Periksa Informasinya', 'Bandingkan detail, kondisi, dan biaya properti sebelum mengambil keputusan.', 'M9 12.5 11 14.5 15.5 9.5M7 3.8h10v16.4H7z'],
                        ['Kenali Pihak Terkait', 'Pastikan identitas pemilik atau agen dan tanyakan dokumen pendukung.', 'M16 20v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V20m7-9a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-7a4 4 0 0 1 0 7.8m4 8.2v-1.5a4 4 0 0 0-3-3.9'],
                        ['Putuskan dengan Tenang', 'Jadwalkan kunjungan dan gunakan pendamping profesional bila diperlukan.', 'M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Zm-3-11 2 2 4-4'],
                    ] as [$title, $description, $iconPath])
                        <article>
                            <svg class="size-6 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"></path>
                            </svg>
                            <h3 class="mt-4 font-display text-lg font-bold">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-white/65">{{ $description }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="relative overflow-hidden bg-secondary px-4 py-16 text-center sm:px-6 lg:py-20">
            <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1800&q=85&auto=format&fit=crop"
                alt="" aria-hidden="true" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-secondary/80"></div>
            <div class="relative mx-auto max-w-3xl">
                <h2 class="font-display text-3xl font-extrabold text-white sm:text-4xl">Siap Menemukan Rumah Impian Anda?</h2>
                <p class="mt-4 text-sm leading-6 text-white/80">Ceritakan kebutuhan Anda. Mari mulai langkah berikutnya bersama Mihom.</p>
                <a href="{{ route('contact') }}" class="mt-7 inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-3 text-sm font-bold text-white transition hover:bg-[#df3f05]">
                    Hubungi Kami
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"></path>
                    </svg>
                </a>
            </div>
        </section>
    </main>

    <x-footer />

    <script>
        const navbar = document.getElementById('navbar');
        const blackLogo = document.getElementById('logo-black');
        const whiteLogo = document.getElementById('logo-white');
        const hamburger = document.getElementById('hamburgerBtn');
        const mobileNav = document.getElementById('mobileNav');

        window.addEventListener('scroll', () => {
            const scrolled = window.scrollY > 60;
            navbar.classList.toggle('scrolled', scrolled);
            blackLogo.classList.toggle('hidden', !scrolled);
            blackLogo.classList.toggle('block', scrolled);
            whiteLogo.classList.toggle('hidden', scrolled);
        });

        function closeMobileNav() {
            mobileNav.classList.remove('open');
            hamburger.classList.remove('open');
            hamburger.setAttribute('aria-expanded', 'false');
        }

        hamburger.addEventListener('click', () => {
            const open = mobileNav.classList.toggle('open');
            hamburger.classList.toggle('open', open);
            hamburger.setAttribute('aria-expanded', String(open));
        });

        document.addEventListener('click', (event) => {
            if (!navbar.contains(event.target) && !mobileNav.contains(event.target)) {
                closeMobileNav();
            }
        });
    </script>
</body>

</html>