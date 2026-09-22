<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Masuk ke akun mihom Anda untuk mengakses ribuan listing properti terbaik di Indonesia.">
    <link rel="icon" href="{{ asset('assets/png/faviconblack.png') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" href="{{ asset('assets/png/faviconwhite.png') }}" media="(prefers-color-scheme: dark)">
    <title>Masuk – mihom</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --orange:       #f97316;
            --orange-dark:  #ea6c0a;
            --orange-light: #fff7ed;
            --orange-glow:  rgba(249,115,22,0.35);
            --navy:         #1a1a2e;
            --navy-mid:     #16213e;
            --text-dark:    #111827;
            --text-mid:     #374151;
            --text-soft:    #6b7280;
            --text-pale:    #9ca3af;
            --border:       #e5e7eb;
            --white:        #ffffff;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--navy);
            overflow-x: hidden;
        }

        /* ── LEFT PANEL ─────────────────────────────────────────── */
        .panel-left {
            flex: 1;
            position: relative;
            display: none;
            overflow: hidden;
        }
        @media (min-width: 1024px) { .panel-left { display: block; } }

        .panel-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .panel-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                160deg,
                rgba(26,26,46,0.90) 0%,
                rgba(26,26,46,0.55) 55%,
                rgba(249,115,22,0.22) 100%
            );
        }

        .panel-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 40px 48px;
        }

        .panel-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .panel-logo-text {
            font-size: 26px;
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.5px;
        }
        .panel-logo-dot {
            width: 8px;
            height: 8px;
            background: var(--orange);
            border-radius: 50%;
            display: inline-block;
            margin-left: 1px;
            vertical-align: middle;
        }

        .panel-hero {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding-bottom: 56px;
        }

        .panel-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(249,115,22,0.18);
            border: 1px solid rgba(249,115,22,0.4);
            color: #fed7aa;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.4px;
            margin-bottom: 20px;
            backdrop-filter: blur(8px);
            width: fit-content;
        }

        .panel-title {
            font-size: clamp(28px, 3.2vw, 42px);
            font-weight: 800;
            color: #fff;
            line-height: 1.15;
            margin-bottom: 18px;
            letter-spacing: -0.8px;
        }
        .panel-title span { color: var(--orange); }

        .panel-desc {
            font-size: 15px;
            color: rgba(255,255,255,0.72);
            line-height: 1.7;
            max-width: 420px;
            margin-bottom: 36px;
        }

        .panel-stats {
            display: flex;
            gap: 32px;
            flex-wrap: wrap;
        }
        .stat-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .stat-num {
            font-size: 22px;
            font-weight: 900;
            color: var(--orange);
        }
        .stat-lbl {
            font-size: 12px;
            color: rgba(255,255,255,0.55);
            font-weight: 500;
        }

        /* ── RIGHT PANEL ─────────────────────────────────────────── */
        .panel-right {
            width: 100%;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 24px;
            position: relative;
            background: var(--navy-mid);
            overflow-y: auto;
        }
        @media (min-width: 1024px) {
            .panel-right {
                min-width: 480px;
                width: 480px;
                flex-shrink: 0;
            }
        }

        .panel-right::before {
            content: '';
            position: absolute;
            top: 35%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 360px;
            height: 360px;
            background: radial-gradient(circle, rgba(249,115,22,0.16) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* ── LOGIN CARD ──────────────────────────────────────────── */
        .login-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 24px;
            padding: 40px 36px;
            backdrop-filter: blur(20px);
            box-shadow:
                0 24px 64px rgba(0,0,0,0.45),
                inset 0 1px 0 rgba(255,255,255,0.08);
        }
        @media (max-width: 480px) {
            .login-card { padding: 32px 20px; border-radius: 20px; }
        }

        /* Mobile logo */
        .mobile-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 28px;
            text-decoration: none;
        }
        .mobile-logo-text {
            font-size: 22px;
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.5px;
        }
        @media (min-width: 1024px) { .mobile-logo { display: none; } }

        .card-header { margin-bottom: 28px; }
        .card-title {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 6px;
        }
        .card-subtitle {
            font-size: 14px;
            color: rgba(255,255,255,0.45);
            line-height: 1.55;
        }

        /* ── TABS ──────────────────────────────────────────────── */
        .auth-tabs {
            display: flex;
            background: rgba(255,255,255,0.06);
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 28px;
            gap: 4px;
        }
        .auth-tab {
            flex: 1;
            padding: 9px 12px;
            border-radius: 9px;
            border: none;
            background: transparent;
            font-size: 14px;
            font-weight: 600;
            color: rgba(255,255,255,0.45);
            cursor: pointer;
            transition: all 0.25s;
            font-family: 'Inter', sans-serif;
        }
        .auth-tab.active {
            background: var(--orange);
            color: #fff;
            box-shadow: 0 4px 14px rgba(249,115,22,0.45);
        }
        .auth-tab:not(.active):hover {
            color: rgba(255,255,255,0.75);
            background: rgba(255,255,255,0.06);
        }

        /* ── FORM PANELS ─────────────────────────────────────────── */
        .form-panel { display: none; }
        .form-panel.active {
            display: block;
            animation: fadeSlide 0.3s ease both;
        }
        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── FORM ELEMENTS ──────────────────────────────────────── */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: rgba(255,255,255,0.50);
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .input-wrap { position: relative; }
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.35);
            font-size: 16px;
            pointer-events: none;
            line-height: 1;
        }

        .form-input {
            width: 100%;
            padding: 13px 14px 13px 42px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            color: #fff;
            outline: none;
            transition: border-color 0.25s, background 0.25s, box-shadow 0.25s;
            font-family: 'Inter', sans-serif;
        }
        .form-input::placeholder { color: rgba(255,255,255,0.22); }
        .form-input:focus {
            border-color: var(--orange);
            background: rgba(249,115,22,0.07);
            box-shadow: 0 0 0 3px rgba(249,115,22,0.18);
        }
        .form-input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 1000px #1a2240 inset;
            -webkit-text-fill-color: #fff;
        }

        /* Phone flag prefix */
        .phone-flag {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            gap: 6px;
            pointer-events: none;
            z-index: 1;
        }
        .flag-emoji { font-size: 15px; line-height: 1; }
        .country-code {
            font-size: 13px;
            font-weight: 700;
            color: rgba(255,255,255,0.55);
            border-right: 1px solid rgba(255,255,255,0.14);
            padding-right: 8px;
        }
        .form-input.phone-input { padding-left: 78px; }

        /* Password toggle */
        .pw-toggle {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: rgba(255,255,255,0.30);
            font-size: 17px;
            line-height: 1;
            transition: color 0.2s;
            padding: 4px;
        }
        .pw-toggle:hover { color: rgba(255,255,255,0.65); }

        /* ── EXTRAS ─────────────────────────────────────────────── */
        .form-extras {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: rgba(255,255,255,0.50);
            cursor: pointer;
            user-select: none;
        }
        .remember-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--orange);
            cursor: pointer;
        }
        .forgot-link {
            font-size: 13px;
            color: var(--orange);
            text-decoration: none;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        .forgot-link:hover { opacity: 0.70; }

        /* ── BUTTONS ─────────────────────────────────────────────── */
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            transition: all 0.25s;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 6px 20px rgba(249,115,22,0.40);
            letter-spacing: 0.2px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(249,115,22,0.50);
            background: linear-gradient(135deg, #ea6c0a 0%, #f97316 100%);
        }
        .btn-primary:active { transform: translateY(0); }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 22px 0;
        }
        .divider-line {
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.09);
        }
        .divider-text {
            font-size: 11px;
            color: rgba(255,255,255,0.28);
            font-weight: 600;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        /* Google button */
        .btn-google {
            width: 100%;
            padding: 13px 16px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            color: rgba(255,255,255,0.80);
            cursor: pointer;
            transition: all 0.25s;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        .btn-google:hover {
            background: rgba(255,255,255,0.12);
            border-color: rgba(255,255,255,0.24);
            color: #fff;
            transform: translateY(-1px);
        }
        .google-logo {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        /* ── FOOTER LINK ─────────────────────────────────────────── */
        .card-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: rgba(255,255,255,0.36);
        }
        .card-footer a {
            color: var(--orange);
            font-weight: 700;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .card-footer a:hover { opacity: 0.70; }

        /* ── TERMS ───────────────────────────────────────────────── */
        .terms-text {
            font-size: 11.5px;
            color: rgba(255,255,255,0.28);
            text-align: center;
            margin-top: 16px;
            line-height: 1.6;
        }
        .terms-text a {
            color: rgba(255,255,255,0.46);
            text-decoration: underline;
            transition: color 0.2s;
        }
        .terms-text a:hover { color: var(--orange); }

        /* ── ALERTS ──────────────────────────────────────────────── */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 18px;
            font-weight: 500;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .alert-error {
            background: rgba(239,68,68,0.14);
            border: 1px solid rgba(239,68,68,0.28);
            color: #fca5a5;
        }
        .alert-success {
            background: rgba(34,197,94,0.14);
            border: 1px solid rgba(34,197,94,0.28);
            color: #86efac;
        }

        /* ── LOADING SPINNER ─────────────────────────────────────── */
        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            display: none;
            flex-shrink: 0;
        }
        .btn-primary.loading .spinner { display: block; }
        .btn-primary.loading .btn-label { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── REGISTER NAME ROW ───────────────────────────────────── */
        .name-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        @media (max-width: 400px) { .name-row { grid-template-columns: 1fr; } }

        /* ── BACK LINK ───────────────────────────────────────────── */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: rgba(255,255,255,0.35);
            text-decoration: none;
            margin-bottom: 24px;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--orange); }
    </style>
</head>
<body>

    <!-- LEFT PANEL -->
    <div class="panel-left">
        <img
            src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1400&auto=format&fit=crop&q=80"
            alt="Properti mihom"
            class="panel-bg"
        >
        <div class="panel-overlay"></div>
        <div class="panel-content">
            <a href="{{ url('/') }}" class="panel-logo">
                <img id="logo-white" src="{{ asset ('assets/png/wht trnsprn.png') }}" alt="Logo" class="h-8">
                <span class="panel-logo-text">mihom<span class="panel-logo-dot"></span></span>
            </a>
            <div class="panel-hero">
                <div class="panel-badge">Pilihan terbaik untuk anda</div>
                <h1 class="panel-title">
                    Temukan Rumah<br>
                    <span>Impian Anda</span><br>
                    Sekarang
                </h1>
                <p class="panel-desc">
                    Lebih dari 10.000 listing properti siap pilih — dari rumah tapak, apartemen,
                    ruko, hingga kavling. Mudah, cepat, dan terpercaya.
                </p>
                <div class="panel-stats">
                    <div class="stat-item">
                        <span class="stat-num">10K+</span>
                        <span class="stat-lbl">Properti Aktif</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-num">8K+</span>
                        <span class="stat-lbl">Pengguna Aktif</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-num">98%</span>
                        <span class="stat-lbl">Kepuasan Pengguna</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL (form) -->
    <div class="panel-right">
        <div class="login-card">

            <!-- Mobile logo -->
            <a href="{{ url('/') }}" class="mobile-logo">
                <span class="mobile-logo-text">mihom<span style="display:inline-block;width:7px;height:7px;background:#f97316;border-radius:50%;margin-left:2px;vertical-align:middle;"></span></span>
            </a>

            <!-- Back link -->
            <a href="{{ url('/') }}" class="back-link">← Kembali ke beranda</a>

            <!-- Header -->
            <div class="card-header">
                <h2 class="card-title" id="card-title-text">Selamat Datang Kembali 👋</h2>
                <p class="card-subtitle" id="card-subtitle-text">Masuk ke akun Anda untuk melanjutkan</p>
            </div>

            <!-- Alerts -->
            @if ($errors->any())
                <div class="alert alert-error">
                    ⚠️ {{ $errors->first() }}
                </div>
            @endif
            @if (session('status'))
                <div class="alert alert-success">
                    ✅ {{ session('status') }}
                </div>
            @endif

            <!-- Tabs -->
            <div class="auth-tabs" role="tablist">
                <button
                    class="auth-tab active"
                    id="tab-login"
                    role="tab"
                    aria-selected="true"
                    aria-controls="panel-login"
                    onclick="switchTab('login')"
                >Masuk</button>
                <button
                    class="auth-tab"
                    id="tab-register"
                    role="tab"
                    aria-selected="false"
                    aria-controls="panel-register"
                    onclick="switchTab('register')"
                >Daftar Akun</button>
            </div>

            <!-- ══════ LOGIN PANEL ══════ -->
            <div class="form-panel active" id="panel-login" role="tabpanel" aria-labelledby="tab-login">
                <form method="POST" action="{{ route('login') }}" id="form-login" onsubmit="handleSubmit(this)">
                    @csrf

                    <!-- Nomor HP -->
                    <div class="form-group">
                        <label class="form-label" for="login-phone">Nomor HP</label>
                        <div class="input-wrap">
                            <div class="phone-flag">
                                <span class="flag-emoji">🇮🇩</span>
                                <span class="country-code">+62</span>
                            </div>
                            <input
                                type="tel"
                                id="login-phone"
                                name="phone"
                                class="form-input phone-input"
                                placeholder="8xx xxxx xxxx"
                                inputmode="numeric"
                                autocomplete="tel"
                                value="{{ old('phone') }}"
                                required
                            >
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label class="form-label" for="login-password">Kata Sandi</label>
                        <div class="input-wrap">
                            <span class="input-icon">🔒</span>
                            <input
                                type="password"
                                id="login-password"
                                name="password"
                                class="form-input"
                                placeholder="Masukkan kata sandi"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="pw-toggle" onclick="togglePw('login-password', this)" aria-label="Tampilkan kata sandi">👁</button>
                        </div>
                    </div>

                    <!-- Remember + Forgot -->
                    <div class="form-extras">
                        <label class="remember-label">
                            <input type="checkbox" name="remember" id="remember-me">
                            Ingat saya
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">Lupa kata sandi?</a>
                        @endif
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-primary" id="btn-login">
                        <div class="spinner"></div>
                        <span class="btn-label">Masuk Sekarang →</span>
                    </button>
                </form>

                <!-- Divider -->
                <div class="divider">
                    <div class="divider-line"></div>
                    <span class="divider-text">atau masuk dengan</span>
                    <div class="divider-line"></div>
                </div>

                <!-- Google -->
                <a href="#" class="btn-google" id="btn-google-login">
                    <svg class="google-logo" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Lanjutkan dengan Google
                </a>
            </div>

            <!-- ══════ REGISTER PANEL ══════ -->
            <div class="form-panel" id="panel-register" role="tabpanel" aria-labelledby="tab-register">
                <form method="POST" action="{{ route('register') }}" id="form-register" onsubmit="handleSubmit(this)">
                    @csrf

                    <!-- Nama -->
                    <div class="name-row">
                        <div class="form-group">
                            <label class="form-label" for="reg-firstname">Nama Depan</label>
                            <div class="input-wrap">
                                <span class="input-icon">👤</span>
                                <input type="text" id="reg-firstname" name="first_name" class="form-input"
                                    placeholder="Budi" autocomplete="given-name"
                                    value="{{ old('first_name') }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="reg-lastname">Nama Belakang</label>
                            <div class="input-wrap">
                                <span class="input-icon">👤</span>
                                <input type="text" id="reg-lastname" name="last_name" class="form-input"
                                    placeholder="Santoso" autocomplete="family-name"
                                    value="{{ old('last_name') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label class="form-label" for="reg-email">Alamat Email</label>
                        <div class="input-wrap">
                            <span class="input-icon">✉️</span>
                            <input type="email" id="reg-email" name="email" class="form-input"
                                placeholder="budi@email.com" autocomplete="email"
                                value="{{ old('email') }}" required>
                        </div>
                    </div>

                    <!-- Nomor HP -->
                    <div class="form-group">
                        <label class="form-label" for="reg-phone">Nomor HP</label>
                        <div class="input-wrap">
                            <div class="phone-flag">
                                <span class="flag-emoji">🇮🇩</span>
                                <span class="country-code">+62</span>
                            </div>
                            <input type="tel" id="reg-phone" name="phone" class="form-input phone-input"
                                placeholder="8xx xxxx xxxx" inputmode="numeric" autocomplete="tel"
                                value="{{ old('phone') }}" required>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label class="form-label" for="reg-password">Kata Sandi</label>
                        <div class="input-wrap">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="reg-password" name="password" class="form-input"
                                placeholder="Min. 8 karakter" autocomplete="new-password"
                                required minlength="8" oninput="checkPasswordStrength(this.value)">
                            <button type="button" class="pw-toggle" onclick="togglePw('reg-password', this)" aria-label="Tampilkan kata sandi">👁</button>
                        </div>
                        <div id="pw-strength-bar" style="margin-top:8px;height:4px;border-radius:4px;background:rgba(255,255,255,0.08);overflow:hidden;display:none;">
                            <div id="pw-strength-fill" style="height:100%;width:0%;border-radius:4px;transition:width 0.3s,background 0.3s;"></div>
                        </div>
                        <p id="pw-strength-text" style="font-size:11px;margin-top:5px;color:rgba(255,255,255,0.35);display:none;"></p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label class="form-label" for="reg-password-confirm">Konfirmasi Kata Sandi</label>
                        <div class="input-wrap">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="reg-password-confirm" name="password_confirmation"
                                class="form-input" placeholder="Ulangi kata sandi"
                                autocomplete="new-password" required>
                            <button type="button" class="pw-toggle" onclick="togglePw('reg-password-confirm', this)" aria-label="Tampilkan kata sandi">👁</button>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-primary" id="btn-register">
                        <div class="spinner"></div>
                        <span class="btn-label">Buat Akun Sekarang →</span>
                    </button>

                    <!-- Divider -->
                    <div class="divider">
                        <div class="divider-line"></div>
                        <span class="divider-text">atau daftar dengan</span>
                        <div class="divider-line"></div>
                    </div>

                    <!-- Google Register -->
                    <a href="#" class="btn-google" id="btn-google-register">
                        <svg class="google-logo" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Daftar dengan Google
                    </a>
                </form>

                <p class="terms-text">
                    Dengan mendaftar, Anda menyetujui
                    <a href="#">Syarat &amp; Ketentuan</a> dan
                    <a href="#">Kebijakan Privasi</a> mihom.
                </p>
            </div>

            <!-- Footer -->
            <p class="card-footer" id="card-footer-text">
                Belum punya akun?
                <a href="#" onclick="switchTab('register'); return false;">Daftar sekarang</a>
            </p>

        </div><!-- /.login-card -->
    </div><!-- /.panel-right -->

    <script>
        // ── Tab switching ─────────────────────────────────────────
        function switchTab(tab) {
            const isLogin = tab === 'login';

            document.getElementById('tab-login').classList.toggle('active', isLogin);
            document.getElementById('tab-register').classList.toggle('active', !isLogin);

            document.getElementById('tab-login').setAttribute('aria-selected', isLogin);
            document.getElementById('tab-register').setAttribute('aria-selected', !isLogin);

            document.getElementById('panel-login').classList.toggle('active', isLogin);
            document.getElementById('panel-register').classList.toggle('active', !isLogin);

            document.getElementById('card-title-text').textContent = isLogin
                ? 'Selamat Datang Kembali \uD83D\uDC4B'
                : 'Buat Akun Baru \uD83D\uDE80';
            document.getElementById('card-subtitle-text').textContent = isLogin
                ? 'Masuk ke akun Anda untuk melanjutkan'
                : 'Isi data di bawah untuk membuat akun mihom';

            document.getElementById('card-footer-text').innerHTML = isLogin
                ? `Belum punya akun? <a href="#" onclick="switchTab('register'); return false;">Daftar sekarang</a>`
                : `Sudah punya akun? <a href="#" onclick="switchTab('login'); return false;">Masuk di sini</a>`;

            // Update URL param without reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            history.replaceState(null, '', url);
        }

        // ── Password visibility toggle ────────────────────────────
        function togglePw(inputId, btn) {
            const input = document.getElementById(inputId);
            const isText = input.type === 'text';
            input.type = isText ? 'password' : 'text';
            btn.textContent = isText ? '\uD83D\uDC41' : '\uD83D\uDE48';
        }

        // ── Password strength ─────────────────────────────────────
        function checkPasswordStrength(pw) {
            const bar  = document.getElementById('pw-strength-bar');
            const fill = document.getElementById('pw-strength-fill');
            const text = document.getElementById('pw-strength-text');

            if (!pw) { bar.style.display = 'none'; text.style.display = 'none'; return; }
            bar.style.display = 'block';
            text.style.display = 'block';

            let score = 0;
            if (pw.length >= 8)        score++;
            if (pw.length >= 12)       score++;
            if (/[A-Z]/.test(pw))      score++;
            if (/[0-9]/.test(pw))      score++;
            if (/[^A-Za-z0-9]/.test(pw)) score++;

            const levels = [
                { label: 'Sangat lemah', color: '#ef4444', pct: '20%' },
                { label: 'Lemah',        color: '#f97316', pct: '40%' },
                { label: 'Cukup',        color: '#eab308', pct: '60%' },
                { label: 'Kuat',         color: '#22c55e', pct: '80%' },
                { label: 'Sangat kuat',  color: '#16a34a', pct: '100%' },
            ];
            const lvl = levels[Math.max(0, score - 1)];
            fill.style.width      = lvl.pct;
            fill.style.background = lvl.color;
            text.style.color      = lvl.color;
            text.textContent      = lvl.label;
        }

        // ── Loading state ─────────────────────────────────────────
        function handleSubmit(form) {
            const btn = form.querySelector('.btn-primary');
            if (btn) btn.classList.add('loading');
        }

        // ── Auto-format phone number ──────────────────────────────
        document.querySelectorAll('input[type="tel"]').forEach(function(input) {
            input.addEventListener('input', function() {
                let v = this.value.replace(/\D/g, '');
                if (v.startsWith('0'))  v = v.slice(1);
                if (v.startsWith('62')) v = v.slice(2);
                this.value = v;
            });
        });

        // ── Init from URL param ───────────────────────────────────
        (function() {
            const tab = new URLSearchParams(window.location.search).get('tab');
            if (tab === 'register') switchTab('register');
        })();
    </script>
</body>
</html>
