<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Masuk ke akun mihom Anda untuk mengakses ribuan listing properti terbaik di Indonesia.">
    <link rel="icon" href="{{ asset('assets/png/faviconblack.ico') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" href="{{ asset('assets/png/faviconwhite.ico') }}" media="(prefers-color-scheme: dark)">
    <title>Masuk - Mihom</title>
    @vite(['resources/css/app.css', 'resources/css/login.css', 'resources/js/app.js'])
</head>
<body class="m-0 flex min-h-screen overflow-x-hidden bg-secondary font-sans text-white">
    <section class="relative hidden flex-1 overflow-hidden lg:block">
        <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1400&auto=format&fit=crop&q=80" alt="Properti Mihom" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-secondary/85"></div>
        <div class="relative z-10 flex h-full flex-col p-10 xl:p-12">
            <a href="{{ url('/') }}" class="flex items-center gap-2 no-underline">
                <img src="{{ asset('assets/png/wht trnsprn.png') }}" alt="Mihom" class="h-8">
                <span class="font-display text-2xl font-extrabold tracking-tight">mihom<span class="ml-1 inline-block h-2 w-2 rounded-full bg-primary align-middle"></span></span>
            </a>
            <div class="flex flex-1 flex-col justify-end pb-14">
                <h1 class="font-display text-4xl font-extrabold leading-tight text-white xl:text-5xl">Temukan Rumah<br><span class="text-primary">Impian Anda</span><br>Sekarang</h1>
                <p class="mb-9 mt-5 max-w-md text-sm leading-7 text-white/70">Lebih dari 10.000 listing properti siap pilih - dari rumah tapak, apartemen, ruko, hingga kavling. Mudah, cepat, dan terpercaya.</p>
                <div class="flex flex-wrap gap-8">
                    @foreach ([['10K+', 'Properti Aktif'], ['8K+', 'Pengguna Aktif'], ['98%', 'Kepuasan Pengguna']] as [$number, $label])
                        <div class="flex flex-col gap-0.5"><span class="font-display text-[22px] font-extrabold text-primary">{{ $number }}</span><span class="text-xs font-medium text-white/55">{{ $label }}</span></div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <main class="flex w-full max-w-[500px] flex-col justify-center overflow-y-auto bg-[#243746] px-6 py-10 lg:min-w-[480px] lg:max-w-[480px] lg:shrink-0">
        <div class="mx-auto w-full max-w-[420px] rounded-3xl border border-white/10 bg-white/5 p-5 shadow-2xl sm:p-9">
            <a href="{{ url('/') }}" class="mb-7 flex items-center justify-center gap-2 lg:hidden"><span class="font-display text-[22px] font-extrabold">mihom<span class="ml-1 inline-block h-2 w-2 rounded-full bg-primary align-middle"></span></span></a>
            <a href="{{ url('/') }}" class="mb-6 inline-flex items-center gap-1.5 text-[13px] text-white/40 transition hover:text-primary">&larr; Kembali ke beranda</a>

            <div class="mb-7"><h2 class="mb-1.5 font-display text-[22px] font-extrabold" id="card-title-text">Selamat Datang Kembali</h2><p class="text-sm leading-6 text-white/45" id="card-subtitle-text">Masuk ke akun Anda untuk melanjutkan</p></div>

            @if ($errors->any())<div class="mb-5 flex items-start gap-2 rounded-lg border border-red-400/30 bg-red-400/15 px-4 py-3 text-[13px] font-medium text-red-200">⚠️ {{ $errors->first() }}</div>@endif
            @if (session('status'))<div class="mb-5 flex items-start gap-2 rounded-lg border border-green-400/30 bg-green-400/15 px-4 py-3 text-[13px] font-medium text-green-200">✅ {{ session('status') }}</div>@endif

            <div class="mb-7 flex gap-1 rounded-xl bg-white/5 p-1" role="tablist">
                <button class="auth-tab active flex-1 rounded-lg border-0 bg-primary px-3 py-2 text-sm font-bold text-white shadow-md shadow-black/10 transition" id="tab-login" role="tab" aria-selected="true" aria-controls="panel-login" onclick="switchTab('login')">Masuk</button>
                <button class="auth-tab flex-1 rounded-lg border-0 bg-transparent px-3 py-2 text-sm font-bold text-white/45 transition hover:bg-white/5 hover:text-white/75" id="tab-register" role="tab" aria-selected="false" aria-controls="panel-register" onclick="switchTab('register')">Daftar Akun</button>
            </div>

            <div class="form-panel active" id="panel-login" role="tabpanel" aria-labelledby="tab-login">
                <form method="POST" action="{{ route('login') }}" id="form-login" onsubmit="handleSubmit(this)">
                    @csrf
                    <div class="mb-[18px]"><label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-white/50" for="login-phone">Nomor HP</label><div class="relative"><div class="pointer-events-none absolute left-3.5 top-1/2 z-10 flex -translate-y-1/2 items-center gap-1.5"><span>🇮🇩</span><span class="border-r border-white/15 pr-2 text-[13px] font-bold text-white/55">+62</span></div><input type="tel" id="login-phone" name="phone" class="form-input w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-3 pl-[78px] text-sm font-medium text-white outline-none transition placeholder:text-white/25 focus:border-primary focus:bg-primary/10 focus:ring-2 focus:ring-primary/20" placeholder="8xx xxxx xxxx" inputmode="numeric" autocomplete="tel" value="{{ old('phone') }}" required></div></div>
                    <div class="mb-[18px]"><label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-white/50" for="login-password">Kata Sandi</label><div class="relative"><span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-base text-white/35"><img src="{{ asset('assets/png/Gembok.svg') }}" alt="Email Icon" class="h-3 w-3"></span><input type="password" id="login-password" name="password" class="form-input w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-3 pl-[42px] text-sm font-medium text-white outline-none transition placeholder:text-white/25 focus:border-primary focus:bg-primary/10 focus:ring-2 focus:ring-primary/20" placeholder="Masukkan kata sandi" autocomplete="current-password" required><button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer border-0 bg-transparent p-1 text-base text-white/30 transition hover:text-white/65" onclick="togglePw('login-password', this)" aria-label="Tampilkan kata sandi"><img src="{{ asset('assets/png/Mata dark.svg') }}" alt="Email Icon" class="h-3 w-3"></button></div></div>
                    <div class="mb-[22px] flex flex-wrap items-center justify-between gap-2.5"><label class="flex cursor-pointer select-none items-center gap-2 text-[13px] text-white/50"><input type="checkbox" name="remember" id="remember-me" class="h-4 w-4 cursor-pointer accent-primary">Ingat saya</label>@if (Route::has('password.request'))<a href="{{ route('password.request') }}" class="text-[13px] font-bold text-primary transition hover:opacity-70">Lupa kata sandi?</a>@endif</div>
                    <button type="submit" class="btn-primary flex w-full items-center justify-center gap-2 rounded-xl border-0 bg-primary px-3.5 py-3.5 text-[15px] font-bold text-white transition hover:-translate-y-0.5 hover:bg-[#df3f05]" id="btn-login"><span class="spinner hidden h-[18px] w-[18px] shrink-0 rounded-full border-2 border-white/30 border-t-white"></span><span class="btn-label">Masuk Sekarang &rarr;</span></button>
                </form>
                <div class="my-[22px] flex items-center gap-3.5"><span class="h-px flex-1 bg-white/10"></span><span class="whitespace-nowrap text-[11px] font-semibold tracking-wide text-white/30">atau masuk dengan</span><span class="h-px flex-1 bg-white/10"></span></div>
                <a href="#" class="flex w-full items-center justify-center gap-2.5 rounded-xl border border-white/15 bg-white/5 px-4 py-3 text-sm font-semibold text-white/80 transition hover:-translate-y-px hover:bg-white/10 hover:text-white"><svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>Lanjutkan dengan Google</a>
            </div>

            <div class="form-panel" id="panel-register" role="tabpanel" aria-labelledby="tab-register">
                <form method="POST" action="{{ route('register') }}" id="form-register" onsubmit="handleSubmit(this)">
                    @csrf
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2"><div class="mb-[18px]"><label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-white/50" for="reg-firstname">Nama Depan</label><div class="relative"><span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2"><img src="{{ asset('assets/png/Person.svg') }}" alt="Email Icon" class="h-3 w-3"></span><input type="text" id="reg-firstname" name="first_name" class="form-input w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-3 pl-[42px] text-sm text-white outline-none transition placeholder:text-white/25 focus:border-primary focus:bg-primary/10 focus:ring-2 focus:ring-primary/20" placeholder="Budi" autocomplete="given-name" value="{{ old('first_name') }}" required></div></div><div class="mb-[18px]"><label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-white/50" for="reg-lastname">Nama Belakang</label><div class="relative"><span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2"><img src="{{ asset('assets/png/Person.svg') }}" alt="Email Icon" class="h-3 w-3"></span><input type="text" id="reg-lastname" name="last_name" class="form-input w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-3 pl-[42px] text-sm text-white outline-none transition placeholder:text-white/25 focus:border-primary focus:bg-primary/10 focus:ring-2 focus:ring-primary/20" placeholder="Santoso" autocomplete="family-name" value="{{ old('last_name') }}"></div></div></div>
                    <div class="mb-[18px]"><label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-white/50" for="reg-email">Alamat Email</label><div class="relative"><span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2"><img src="{{ asset('assets/png/email.svg') }}" alt="Email Icon" class="h-3 w-3"></span><input type="email" id="reg-email" name="email" class="form-input w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-3 pl-[42px] text-sm text-white outline-none transition placeholder:text-white/25 focus:border-primary focus:bg-primary/10 focus:ring-2 focus:ring-primary/20" placeholder="budi@email.com" autocomplete="email" value="{{ old('email') }}" required></div></div>
                    <div class="mb-[18px]"><label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-white/50" for="reg-phone">Nomor HP</label><div class="relative"><div class="pointer-events-none absolute left-3.5 top-1/2 z-10 flex -translate-y-1/2 items-center gap-1.5"><span>🇮🇩</span><span class="border-r border-white/15 pr-2 text-[13px] font-bold text-white/55">+62</span></div><input type="tel" id="reg-phone" name="phone" class="form-input w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-3 pl-[78px] text-sm text-white outline-none transition placeholder:text-white/25 focus:border-primary focus:bg-primary/10 focus:ring-2 focus:ring-primary/20" placeholder="8xx xxxx xxxx" inputmode="numeric" autocomplete="tel" value="{{ old('phone') }}" required></div></div>
                    <div class="mb-[18px]"><label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-white/50" for="reg-password">Kata Sandi</label><div class="relative"><span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2"><img src="{{ asset('assets/png/Gembok.svg') }}" alt="Email Icon" class="h-3 w-3"></span><input type="password" id="reg-password" name="password" class="form-input w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-3 pl-[42px] text-sm text-white outline-none transition placeholder:text-white/25 focus:border-primary focus:bg-primary/10 focus:ring-2 focus:ring-primary/20" placeholder="Min. 8 karakter" autocomplete="new-password" required minlength="8" oninput="checkPasswordStrength(this.value)"><button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer border-0 bg-transparent p-1 text-base text-white/30 hover:text-white/65" onclick="togglePw('reg-password', this)" aria-label="Tampilkan kata sandi"><img src="{{ asset('assets/png/Mata dark.svg') }}" alt="Email Icon" class="h-3 w-3"></button></div><div id="pw-strength-bar" class="mt-2 hidden h-1 overflow-hidden rounded bg-white/10"><div id="pw-strength-fill" class="h-full w-0 rounded transition-[width,background-color] duration-300"></div></div><p id="pw-strength-text" class="mt-1 hidden text-[11px]"></p></div>
                    <div class="mb-[18px]"><label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-white/50" for="reg-password-confirm">Konfirmasi Kata Sandi</label><div class="relative"><span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2"><img src="{{ asset('assets/png/Gembok.svg') }}" alt="Email Icon" class="h-3 w-3"></span><input type="password" id="reg-password-confirm" name="password_confirmation" class="form-input w-full rounded-xl border border-white/10 bg-white/5 px-3.5 py-3 pl-[42px] text-sm text-white outline-none transition placeholder:text-white/25 focus:border-primary focus:bg-primary/10 focus:ring-2 focus:ring-primary/20" placeholder="Ulangi kata sandi" autocomplete="new-password" required><button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer border-0 bg-transparent p-1 text-base text-white/30 hover:text-white/65" onclick="togglePw('reg-password-confirm', this)" aria-label="Tampilkan kata sandi"><img src="{{ asset('assets/png/Mata dark.svg') }}" alt="Email Icon" class="h-3 w-3"></button></div></div>
                    <button type="submit" class="btn-primary flex w-full items-center justify-center gap-2 rounded-xl border-0 bg-primary px-3.5 py-3.5 text-[15px] font-bold text-white transition hover:-translate-y-0.5 hover:bg-[#df3f05]" id="btn-register"><span class="spinner hidden h-[18px] w-[18px] shrink-0 rounded-full border-2 border-white/30 border-t-white"></span><span class="btn-label">Buat Akun Sekarang &rarr;</span></button>
                    <div class="my-[22px] flex items-center gap-3.5"><span class="h-px flex-1 bg-white/10"></span><span class="whitespace-nowrap text-[11px] font-semibold tracking-wide text-white/30">atau daftar dengan</span><span class="h-px flex-1 bg-white/10"></span></div>
                    <a href="#" class="flex w-full items-center justify-center gap-2.5 rounded-xl border border-white/15 bg-white/5 px-4 py-3 text-sm font-semibold text-white/80 transition hover:-translate-y-px hover:bg-white/10 hover:text-white">Daftar dengan Google</a>
                </form>
                <p class="mt-4 text-center text-[11px] leading-5 text-white/30">Dengan mendaftar, Anda menyetujui <a href="#" class="underline transition hover:text-primary">Syarat &amp; Ketentuan</a> dan <a href="#" class="underline transition hover:text-primary">Kebijakan Privasi</a> Mihom.</p>
            </div>

            <p class="mt-6 text-center text-[13px] text-white/40" id="card-footer-text">Belum punya akun? <a href="#" class="font-bold text-primary transition hover:opacity-70" onclick="switchTab('register'); return false;">Daftar sekarang</a></p>
        </div>
    </main>

    <script>
        function switchTab(tab) {
            const isLogin = tab === 'login';
            document.getElementById('tab-login').classList.toggle('bg-primary', isLogin);
            document.getElementById('tab-login').classList.toggle('text-white', isLogin);
            document.getElementById('tab-login').classList.toggle('text-white/45', !isLogin);
            document.getElementById('tab-register').classList.toggle('bg-primary', !isLogin);
            document.getElementById('tab-register').classList.toggle('text-white', !isLogin);
            document.getElementById('tab-register').classList.toggle('text-white/45', isLogin);
            document.getElementById('tab-login').setAttribute('aria-selected', isLogin);
            document.getElementById('tab-register').setAttribute('aria-selected', !isLogin);
            document.getElementById('panel-login').classList.toggle('active', isLogin);
            document.getElementById('panel-register').classList.toggle('active', !isLogin);
            document.getElementById('card-title-text').textContent = isLogin ? 'Selamat Datang Kembali' : 'Buat Akun Baru';
            document.getElementById('card-subtitle-text').textContent = isLogin ? 'Masuk ke akun Anda untuk melanjutkan' : 'Isi data di bawah untuk membuat akun Mihom';
            document.getElementById('card-footer-text').innerHTML = isLogin ? 'Belum punya akun? <a href="#" class="font-bold text-primary transition hover:opacity-70" onclick="switchTab(\'register\'); return false;">Daftar sekarang</a>' : 'Sudah punya akun? <a href="#" class="font-bold text-primary transition hover:opacity-70" onclick="switchTab(\'login\'); return false;">Masuk di sini</a>';
            const url = new URL(window.location); url.searchParams.set('tab', tab); history.replaceState(null, '', url);
        }
        function togglePw(inputId, button) { const input = document.getElementById(inputId); const isText = input.type === 'text'; input.type = isText ? 'password' : 'text'; button.innerHTML = isText ? '<img src="{{ asset('assets/png/Mata dark.svg') }}" alt="Email Icon" class="h-3 w-3">' : '<img src="{{ asset('assets/png/Mata light.svg') }}" alt="Email Icon" class="h-3 w-3">'; }
        function checkPasswordStrength(password) {
            const bar = document.getElementById('pw-strength-bar'); const fill = document.getElementById('pw-strength-fill'); const text = document.getElementById('pw-strength-text');
            if (!password) { bar.classList.add('hidden'); text.classList.add('hidden'); return; }
            bar.classList.remove('hidden'); text.classList.remove('hidden'); let score = 0;
            if (password.length >= 8) score++; if (password.length >= 12) score++; if (/[A-Z]/.test(password)) score++; if (/[0-9]/.test(password)) score++; if (/[^A-Za-z0-9]/.test(password)) score++;
            const levels = [{ label: 'Sangat lemah', color: '#ef4444', width: '20%' }, { label: 'Lemah', color: '#FC4907', width: '40%' }, { label: 'Cukup', color: '#eab308', width: '60%' }, { label: 'Kuat', color: '#22c55e', width: '80%' }, { label: 'Sangat kuat', color: '#16a34a', width: '100%' }]; const level = levels[Math.max(0, score - 1)];
            fill.style.width = level.width; fill.style.backgroundColor = level.color; text.style.color = level.color; text.textContent = level.label;
        }
        function handleSubmit(form) { const button = form.querySelector('.btn-primary'); if (button) button.classList.add('loading'); }
        document.querySelectorAll('input[type="tel"]').forEach((input) => input.addEventListener('input', function () { let value = this.value.replace(/\D/g, ''); if (value.startsWith('0')) value = value.slice(1); if (value.startsWith('62')) value = value.slice(2); this.value = value; }));
        (() => { if (new URLSearchParams(window.location.search).get('tab') === 'register') switchTab('register'); })();
    </script>
</body>
</html>
