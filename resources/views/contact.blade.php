<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Hubungi tim Mihom untuk bertanya, berdiskusi, atau merencanakan kunjungan properti.">
    <link rel="icon" href="{{ asset('assets/png/favicongray.ico') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" href="{{ asset('assets/png/faviconwhite.ico') }}" media="(prefers-color-scheme: dark)">
    <title>Kontak - Mihom</title>
    @vite(['resources/css/app.css', 'resources/css/welcome.css', 'resources/js/app.js'])
</head>

<body class="m-0 bg-white font-sans text-secondary antialiased">
    <x-navbar />

    <div class="mobile-nav fixed inset-x-0 top-16 z-40 hidden flex-col gap-1 border-b border-gray-100 bg-white/95 p-4 shadow-xl backdrop-blur-md md:hidden"
        id="mobileNav">
        <a href="{{ route('home') }}" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Beranda</a>
        <a href="{{ route('properties') }}" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Properti</a>
        <a href="{{ route('tax-calculator') }}" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Kalkulator Pajak</a>
        <a href="{{ route('about') }}" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Tentang Kami</a>
        <a href="{{ route('contact') }}" class="rounded-lg px-4 py-3 text-sm font-semibold text-primary hover:bg-[#fff0eb]" onclick="closeMobileNav()">Kontak</a>
        @guest
            <a href="{{ route('login') }}" class="mt-2 rounded-lg bg-primary px-5 py-3 text-center text-sm font-bold text-white">Daftar / Masuk</a>
        @endguest
        @auth
            <a href="{{ route('dashboard') }}" class="mt-2 rounded-lg bg-primary px-5 py-3 text-center text-sm font-bold text-white">{{ Auth::user()->name }}</a>
        @endauth
    </div>

    <main>
        <header class="relative flex min-h-[340px] items-center overflow-hidden bg-secondary pt-16 sm:min-h-[390px]">
            <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1800&q=85&auto=format&fit=crop"
                alt="Rumah modern dengan taman dan kolam renang" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-[linear-gradient(110deg,rgb(31_45_58/88%),rgb(44_62_80/68%)_58%,rgb(44_62_80/38%))]"></div>
            <div class="relative z-10 mx-auto w-full max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <nav aria-label="Breadcrumb" class="mb-5 text-sm text-white/70">
                    <a href="{{ route('home') }}" class="transition hover:text-white">Beranda</a>
                    <span class="mx-2" aria-hidden="true">›</span>
                    <span aria-current="page" class="text-white">Kontak</span>
                </nav>
                <h1 class="max-w-2xl font-display text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Mari Bicara tentang Properti Anda
                </h1>
                <p class="mt-5 max-w-xl text-base leading-7 text-white/80">
                    Punya pertanyaan atau ingin berdiskusi? Temukan cara yang paling nyaman untuk menghubungi kami.
                </p>
            </div>
        </header>

        <section class="px-4 py-10 sm:px-6 lg:py-12">
            <div class="mx-auto max-w-7xl">
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ([
                        ['WhatsApp', '08xx-xxxx-xxxx', 'Untuk percakapan awal dan pertanyaan singkat.', 'Chat via WhatsApp', 'M21 11.5a8.4 8.4 0 0 1-1 4 8.5 8.5 0 0 1-7.5 4.5 8.4 8.4 0 0 1-4-.9L3 20l.9-5.5a8.4 8.4 0 0 1-.9-4A8.5 8.5 0 0 1 11.5 2h.2a8.5 8.5 0 0 1 9.3 9.5Z', 'whatsapp'],
                        ['Email', 'halo@mihom.example', 'Untuk pertanyaan lengkap atau kerja sama.', 'Kirim Email', 'M4 5h16v14H4z M4 7l8 6 8-6', 'email'],
                        ['Telepon', '(021) xxxx-xxxx', 'Untuk berdiskusi langsung dengan tim kami.', 'Hubungi Tim Kami', 'M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.4 19.4 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7l.5 2.8a2 2 0 0 1-.6 1.9L7.1 10a16 16 0 0 0 6 6l1.6-1.9a2 2 0 0 1 1.9-.6l2.8.5a2 2 0 0 1 1.6 1.9Z', 'phone'],
                    ] as [$title, $detail, $description, $linkText, $iconPath, $type])
                        <article class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:-translate-y-1 hover:shadow-lg">
                            <div class="flex items-center gap-3">
                                <span class="flex size-11 items-center justify-center rounded-xl bg-[#fff0eb] text-primary">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"></path>
                                    </svg>
                                </span>
                                <h2 class="font-display text-lg font-bold text-secondary">{{ $title }}</h2>
                            </div>
                            <p class="mt-4 text-sm font-bold text-secondary">{{ $detail }}</p>
                            <p class="mt-2 text-xs leading-5 text-slate-400">{{ $description }}</p>
                            <a href="{{ $type === 'email' ? 'mailto:' . $detail : ($type === 'phone' ? 'tel:' . $detail : '#pesan') }}"
                                class="mt-3 inline-flex items-center gap-2 text-sm font-bold text-primary transition hover:text-[#df3f05]">
                                {{ $linkText }}
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"></path>
                                </svg>
                            </a>
                        </article>
                    @endforeach
                </div>
                <p class="mt-4 flex items-start gap-2 text-xs leading-5 text-slate-400">
                    <svg class="mt-0.5 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"></circle><path stroke-linecap="round" d="M12 11v5m0-8h.01"></path>
                    </svg>
                    Nomor telepon dan alamat email di halaman ini adalah contoh, bukan kontak resmi Mihom.
                </p>
            </div>
        </section>

        <section class="bg-slate-50 px-4 py-14 sm:px-6 lg:py-16" id="pesan">
            <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.72fr_1.28fr] lg:gap-14">
                <aside>
                    <p class="mb-3 text-xs font-extrabold uppercase tracking-[0.18em] text-primary">Kirim Pesan</p>
                    <h2 class="font-display text-3xl font-extrabold leading-tight text-secondary">Apa yang Bisa Kami Bantu?</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-500">Sampaikan pertanyaan Anda melalui formulir ini. Mulai dari kebutuhan sederhana, kita cari arah berikutnya bersama.</p>

                    <div class="mt-7 space-y-5">
                        @foreach ([
                            ['Ceritakan kebutuhan Anda', 'Beli, sewa, jual, atau sekadar mencari informasi.', 'M4 4h16v16H4z M8 9h8M8 13h6'],
                            ['Sertakan lokasi dan anggaran', 'Bantu kami memahami pilihan yang ingin Anda jelajahi.', 'M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z M12 10a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z'],
                            ['Pilih waktu yang nyaman', 'Tuliskan waktu yang Anda inginkan untuk dihubungi.', 'M8 3v3m8-3v3M4 9h16M5 5h14v16H5z'],
                        ] as [$title, $description, $iconPath])
                            <div class="flex gap-3">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#fff0eb] text-primary">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"></path>
                                    </svg>
                                </span>
                                <div>
                                    <h3 class="text-sm font-bold text-secondary">{{ $title }}</h3>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ $description }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-7 rounded-xl border border-slate-200 bg-white p-4">
                        <p class="flex gap-2 text-xs leading-5 text-slate-500">
                            <svg class="mt-0.5 size-4 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Zm-3-11 2 2 4-4"></path>
                            </svg>
                            Tidak perlu menyertakan nomor identitas, dokumen pribadi, atau informasi pembayaran dalam pesan awal.
                        </p>
                    </div>
                </aside>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
                    <h2 class="font-display text-2xl font-bold text-secondary">Tinggalkan Pesan Anda</h2>
                    <p class="mt-2 text-xs text-slate-500">Kolom bertanda <span class="text-primary">*</span> wajib diisi.</p>
                    <form class="mt-6" id="contactForm">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block text-xs font-semibold text-secondary">
                                Nama lengkap <span class="text-primary">*</span>
                                <input required name="name" autocomplete="name" placeholder="Nama Anda"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 px-3 py-3 text-sm font-normal outline-none transition placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/15">
                            </label>
                            <label class="block text-xs font-semibold text-secondary">
                                Email <span class="text-primary">*</span>
                                <input required type="email" name="email" autocomplete="email" placeholder="nama@email.com"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 px-3 py-3 text-sm font-normal outline-none transition placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/15">
                            </label>
                            <label class="block text-xs font-semibold text-secondary">
                                Nomor WhatsApp
                                <input type="tel" name="phone" autocomplete="tel" placeholder="Contoh: 08xx-xxxx-xxxx"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 px-3 py-3 text-sm font-normal outline-none transition placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/15">
                            </label>
                            <label class="block text-xs font-semibold text-secondary">
                                Topik pertanyaan <span class="text-primary">*</span>
                                <select required name="topic"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-3 py-3 text-sm font-normal text-slate-500 outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15">
                                    <option value="" selected disabled>Pilih topik</option>
                                    <option value="buy">Beli properti</option>
                                    <option value="rent">Sewa properti</option>
                                    <option value="sell">Jual properti</option>
                                    <option value="partnership">Kerja sama</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </label>
                            <label class="block text-xs font-semibold text-secondary sm:col-span-2">
                                Subjek <span class="text-primary">*</span>
                                <input required name="subject" placeholder="Contoh: Konsultasi rumah pertama"
                                    class="mt-2 block w-full rounded-lg border border-slate-200 px-3 py-3 text-sm font-normal outline-none transition placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/15">
                            </label>
                            <label class="block text-xs font-semibold text-secondary sm:col-span-2">
                                Pesan <span class="text-primary">*</span>
                                <textarea required name="message" rows="5" placeholder="Ceritakan kebutuhan atau pertanyaan Anda di sini..."
                                    class="mt-2 block w-full resize-y rounded-lg border border-slate-200 px-3 py-3 text-sm font-normal outline-none transition placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/15"></textarea>
                            </label>
                        </div>
                        <label class="mt-4 flex items-start gap-2 text-xs leading-5 text-slate-500">
                            <input required type="checkbox" name="privacy" class="mt-0.5 size-4 shrink-0 accent-primary">
                            <span>Saya setuju data di atas digunakan untuk menanggapi pertanyaan saya sesuai <a href="#" class="font-semibold text-primary hover:underline">Kebijakan Privasi</a>.</span>
                        </label>
                        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-3 text-sm font-bold text-white transition hover:bg-[#df3f05] focus:outline-none focus:ring-2 focus:ring-primary/40 focus:ring-offset-2">
                                Kirim Pesan
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m22 2-7 20-4-9-9-4 20-7ZM22 2 11 13"></path>
                                </svg>
                            </button>
                            <span class="text-xs text-slate-400">Formulir contoh</span>
                        </div>
                        <p class="mt-4 hidden rounded-lg bg-[#fff0eb] px-4 py-3 text-sm leading-5 text-secondary" id="contactFormNotice" role="status">
                            Formulir ini masih berupa contoh dan belum mengirimkan pesan. Silakan gunakan kontak resmi Mihom setelah tersedia.
                        </p>
                    </form>
                </div>
            </div>
        </section>

        <section class="px-4 py-14 sm:px-6 lg:py-16">
            <div class="mx-auto grid max-w-7xl items-center gap-8 lg:grid-cols-[0.72fr_1.28fr] lg:gap-14">
                <div>
                    <p class="mb-3 text-xs font-extrabold uppercase tracking-[0.18em] text-primary">Lokasi Kami</p>
                    <h2 class="font-display text-3xl font-extrabold text-secondary">Mari Bertemu &amp; Berdiskusi</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-500">Untuk kunjungan, hubungi tim terlebih dahulu agar waktu pertemuan dapat disepakati.</p>
                    <div class="mt-6 space-y-5">
                        <div class="flex gap-3">
                            <svg class="mt-0.5 size-5 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"></path><circle cx="12" cy="10" r="2.5"></circle>
                            </svg>
                            <div>
                                <h3 class="text-sm font-bold">Alamat kantor (contoh)</h3>
                                <p class="mt-1 text-sm leading-6 text-slate-500">Jl. Contoh Properti No. 00<br>Kota Contoh, Indonesia</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <svg class="mt-0.5 size-5 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <circle cx="12" cy="12" r="9"></circle><path stroke-linecap="round" d="M12 7v5l3 2"></path>
                            </svg>
                            <div>
                                <h3 class="text-sm font-bold">Jam layanan (contoh)</h3>
                                <p class="mt-1 text-sm leading-6 text-slate-500">Senin–Jumat · 09.00–17.00 WIB<br>Kunjungan dengan janji temu.</p>
                            </div>
                        </div>
                    </div>
                    <p class="mt-5 flex items-start gap-2 text-xs leading-5 text-slate-400">
                        <svg class="mt-0.5 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"></circle><path stroke-linecap="round" d="M12 11v5m0-8h.01"></path>
                        </svg>
                        Alamat, jam layanan, dan peta perlu diganti dengan informasi resmi.
                    </p>
                </div>
                <div class="relative flex min-h-64 items-center justify-center overflow-hidden rounded-2xl bg-[#edf2ee] p-6 sm:min-h-80" role="img" aria-label="Ilustrasi peta lokasi kantor Mihom">
                    <div class="absolute inset-0 grid grid-cols-4 grid-rows-3 gap-2 p-3 opacity-80">
                        <span class="rounded-lg bg-white"></span><span class="rounded-lg bg-[#cfdfd2]"></span><span class="rounded-lg bg-white"></span><span class="rounded-lg bg-[#dce5df]"></span>
                        <span class="rounded-lg bg-[#dce5df]"></span><span class="rounded-lg bg-white"></span><span class="rounded-lg bg-[#cfdfd2]"></span><span class="rounded-lg bg-white"></span>
                        <span class="rounded-lg bg-white"></span><span class="rounded-lg bg-[#dce5df]"></span><span class="rounded-lg bg-white"></span><span class="rounded-lg bg-[#cfdfd2]"></span>
                    </div>
                    <div class="absolute inset-x-0 top-1/2 h-3 -translate-y-1/2 bg-orange-200"></div>
                    <div class="absolute left-1/2 top-1/2 z-10 -translate-x-1/2 -translate-y-1/2">
                        <span class="flex size-12 items-center justify-center rounded-full border-4 border-white bg-primary text-white shadow-lg ring-8 ring-primary/15">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"></path><circle cx="12" cy="10" r="2.5"></circle>
                            </svg>
                        </span>
                        <span class="absolute left-1/2 top-14 -translate-x-1/2 whitespace-nowrap rounded-md bg-white px-3 py-2 text-xs font-bold text-secondary shadow">Contoh lokasi kantor</span>
                    </div>
                    <span class="absolute bottom-3 left-3 rounded bg-white/90 px-2 py-1 text-[10px] text-slate-500">Peta ilustrasi — bukan lokasi sebenarnya</span>
                </div>
            </div>
        </section>

        <section class="bg-slate-50 px-4 py-14 sm:px-6 lg:py-16">
            <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.72fr_1.28fr] lg:gap-14">
                <div>
                    <p class="mb-3 text-xs font-extrabold uppercase tracking-[0.18em] text-primary">Pertanyaan Umum</p>
                    <h2 class="font-display text-3xl font-extrabold text-secondary">Sebelum Anda Menghubungi Kami</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-500">Beberapa hal yang dapat membantu Anda memulai percakapan.</p>
                </div>
                <div class="space-y-3">
                    @foreach ([
                        ['Apa yang perlu saya siapkan sebelum konsultasi?', 'Mulai dengan lokasi yang Anda inginkan, kisaran anggaran, dan tujuan Anda: membeli, menyewa, atau menjual. Jika belum yakin, tuliskan hal yang ingin Anda ketahui.'],
                        ['Bagaimana cara menjadwalkan kunjungan properti?', 'Sampaikan properti yang ingin dikunjungi dan pilihan waktu Anda. Tim akan membantu mengoordinasikan jadwal dengan pihak terkait.'],
                        ['Apakah saya bisa menghubungi Mihom untuk kerja sama?', 'Tentu. Pilih topik kerja sama pada formulir atau hubungi kami melalui kanal kontak resmi yang tersedia.'],
                    ] as $index => [$question, $answer])
                        <details class="group rounded-xl border border-slate-200 bg-white p-4 sm:p-5" {{ $index === 0 ? 'open' : '' }}>
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-bold text-secondary">
                                {{ $question }}
                                <span class="text-xl font-normal leading-none text-primary transition group-open:rotate-45" aria-hidden="true">+</span>
                            </summary>
                            <p class="mt-3 pr-8 text-sm leading-6 text-slate-500">{{ $answer }}</p>
                        </details>
                    @endforeach
                </div>
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

        document.getElementById('contactForm').addEventListener('submit', (event) => {
            event.preventDefault();
            document.getElementById('contactFormNotice').classList.remove('hidden');
        });
    </script>
</body>

</html>