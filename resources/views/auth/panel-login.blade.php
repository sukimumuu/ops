<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Masuk ke panel administrator Mihom untuk Superadmin, Admin, dan PPAT.">
    <link rel="icon" href="{{ asset('assets/png/faviconblack.ico') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" href="{{ asset('assets/png/faviconwhite.ico') }}" media="(prefers-color-scheme: dark)">
    <title>Panel Administrator - Mihom</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen items-center justify-center bg-secondary px-4 py-10 font-sans text-slate-700">
    <main class="w-full max-w-md">
        <div class="mb-6 flex justify-center">
            <a href="{{ url('/') }}" class="flex items-center gap-2 no-underline">
                <img src="{{ asset('assets/png/wht trnsprn.png') }}" alt="Mihom" class="h-9">
                <span class="font-display text-2xl font-extrabold tracking-tight text-white">mihom<span
                        class="ml-1 inline-block h-2 w-2 rounded-full bg-primary align-middle"></span></span>
            </a>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-2xl sm:p-8">
            <div class="mb-6">
                <span
                    class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-primary">
                    Panel Administrator
                </span>
                <h1 class="mt-3 font-display text-2xl font-extrabold text-secondary">Masuk ke Panel</h1>
                <p class="mt-1 text-sm text-slate-500">Khusus Superadmin, Admin, dan PPAT.</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] font-medium text-red-700"
                    role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('status'))
                <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-[13px] font-medium text-green-700"
                    role="status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('panel.login.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-500"
                        for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20"
                        placeholder="nama@mihom.id" autocomplete="email" autofocus required>
                </div>

                <div class="mb-4">
                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-500"
                        for="password">Kata Sandi</label>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 pr-20 text-sm font-medium text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20"
                            placeholder="Masukkan kata sandi" autocomplete="current-password" required>
                        <button type="button" id="toggle-password"
                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-500 transition hover:bg-slate-100 hover:text-secondary"
                            aria-label="Tampilkan kata sandi" aria-pressed="false">Lihat</button>
                    </div>
                </div>

                <div class="mb-5 flex items-center gap-2">
                    <input type="checkbox" id="remember" name="remember" value="1"
                        class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary/30">
                    <label for="remember" class="text-[13px] font-medium text-slate-500">Ingat saya</label>
                </div>

                <button type="submit"
                    class="w-full rounded-xl bg-primary px-4 py-3 text-sm font-bold text-white transition hover:bg-[#df3f05] focus:outline-none focus:ring-2 focus:ring-primary/40 focus:ring-offset-2">
                    Masuk
                </button>
            </form>

            <div class="mt-6 flex flex-col gap-2 border-t border-slate-100 pt-5 text-center text-[13px]">
                <a href="{{ route('login') }}" class="font-semibold text-secondary transition hover:text-primary">
                    Masuk sebagai pengguna
                </a>
                <a href="{{ route('home') }}" class="text-slate-400 transition hover:text-secondary">
                    &larr; Kembali ke beranda
                </a>
            </div>
        </div>

        <p class="mt-4 text-center text-xs text-white/40">&copy; {{ date('Y') }} Mihom. Hak cipta dilindungi.</p>
    </main>

    <script>
        (function() {
            const password = document.getElementById('password');
            const toggle = document.getElementById('toggle-password');

            toggle.addEventListener('click', function() {
                const isVisible = password.type === 'text';

                password.type = isVisible ? 'password' : 'text';
                toggle.textContent = isVisible ? 'Lihat' : 'Sembunyikan';
                toggle.setAttribute('aria-pressed', String(!isVisible));
                toggle.setAttribute('aria-label', isVisible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
            });
        })();
    </script>
</body>

</html>
