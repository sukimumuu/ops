<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Nihom – Platform properti terpercaya untuk menemukan rumah impian Anda di Indonesia. Lebih dari 10.000 listing siap pilih.">
    <title>Nihom – Temukan Properti Impian Anda</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ==============================
           NAVBAR
        ============================== */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            transition: background 0.3s, box-shadow 0.3s;
        }
        .navbar.scrolled {
            background: rgba(255,255,255,0.97);
            box-shadow: 0 2px 20px rgba(0,0,0,0.10);
        }
        .navbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            height: 64px;
        }
        .nav-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .nav-logo-text {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
            transition: color 0.3s;
        }
        .navbar.scrolled .nav-logo-text { color: #1a1a2e; }
        .nav-logo-icon {
            width: 32px;
            height: 32px;
            background: #f97316;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
        }
        .nav-link {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            transition: all 0.2s;
        }
        .navbar.scrolled .nav-link { color: #374151; }
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }
        .navbar.scrolled .nav-link:hover,
        .navbar.scrolled .nav-link.active {
            background: #fff7ed;
            color: #f97316;
        }
        .btn-nav-cta {
            background: #f97316;
            color: #fff;
            padding: 9px 22px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 2px 10px rgba(249,115,22,0.4);
        }
        .btn-nav-cta:hover {
            background: #ea6c0a;
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(249,115,22,0.45);
        }

        /* Hamburger Button */
        .nav-hamburger {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
            border: none;
            background: transparent;
            cursor: pointer;
            gap: 5px;
            border-radius: 8px;
            transition: background 0.2s;
            padding: 0;
        }
        .nav-hamburger:hover { background: rgba(255,255,255,0.12); }
        .navbar.scrolled .nav-hamburger:hover { background: #f3f4f6; }
        .hamburger-line {
            width: 22px;
            height: 2px;
            background: #fff;
            border-radius: 2px;
            transition: all 0.3s;
        }
        .navbar.scrolled .hamburger-line { background: #374151; }
        .nav-hamburger.open .hamburger-line:nth-child(1) {
            transform: translateY(7px) rotate(45deg);
        }
        .nav-hamburger.open .hamburger-line:nth-child(2) {
            opacity: 0;
        }
        .nav-hamburger.open .hamburger-line:nth-child(3) {
            transform: translateY(-7px) rotate(-45deg);
        }

        /* Mobile Nav Drawer */
        .mobile-nav {
            display: none;
            position: fixed;
            top: 64px;
            left: 0;
            right: 0;
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid #f3f4f6;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            z-index: 99;
            padding: 16px 20px 24px;
            flex-direction: column;
            gap: 4px;
        }
        .mobile-nav.open { display: flex; }
        .mobile-nav-link {
            display: block;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 500;
            color: #374151;
            text-decoration: none;
            transition: all 0.2s;
        }
        .mobile-nav-link:hover, .mobile-nav-link.active {
            background: #fff7ed;
            color: #f97316;
        }
        .mobile-nav-cta {
            display: block;
            margin-top: 12px;
            background: #f97316;
            color: #fff;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            text-align: center;
            box-shadow: 0 4px 16px rgba(249,115,22,0.4);
        }

        /* ==============================
           HERO
        ============================== */
        .hero-section {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            overflow: hidden;
        }
        .hero-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0.70) 0%, rgba(0,0,0,0.35) 60%, rgba(0,0,0,0.20) 100%);
        }
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            padding: 120px 24px 80px;
            width: 100%;
            box-sizing: border-box;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(249,115,22,0.18);
            border: 1px solid rgba(249,115,22,0.4);
            color: #fed7aa;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
            backdrop-filter: blur(8px);
        }
        .hero-title {
            font-size: clamp(36px, 6vw, 64px);
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
            margin-bottom: 18px;
            letter-spacing: -1px;
        }
        .hero-subtitle {
            font-size: 17px;
            color: rgba(255,255,255,0.82);
            max-width: 560px;
            line-height: 1.65;
            margin-bottom: 40px;
        }

        /* Search Box */
        .search-box {
            background: #fff;
            border-radius: 16px;
            padding: 0;
            max-width: 820px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .search-tabs {
            display: flex;
            border-bottom: 1px solid #f3f4f6;
        }
        .search-tab {
            padding: 14px 28px;
            font-size: 14px;
            font-weight: 600;
            color: #9ca3af;
            border: none;
            background: transparent;
            cursor: pointer;
            transition: all 0.2s;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
        }
        .search-tab.active {
            color: #f97316;
            border-bottom-color: #f97316;
        }
        .search-fields {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            flex-wrap: wrap;
            gap: 8px;
        }
        .search-field {
            flex: 1;
            min-width: 160px;
        }
        .field-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .field-select {
            width: 100%;
            border: none;
            outline: none;
            font-size: 14px;
            font-weight: 500;
            color: #111827;
            background: transparent;
            cursor: pointer;
        }
        .search-divider {
            width: 1px;
            height: 40px;
            background: #e5e7eb;
            flex-shrink: 0;
        }
        .btn-search {
            background: #f97316;
            color: #fff;
            border: none;
            padding: 14px 28px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(249,115,22,0.4);
            width: 100%;
        }
        .btn-search:hover {
            background: #ea6c0a;
            transform: translateY(-1px);
        }

        /* ==============================
           COMMON SECTION STYLES
        ============================== */
        .section {
            padding: 80px 24px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            box-sizing: border-box;
        }
        .section-tag {
            font-size: 12px;
            font-weight: 700;
            color: #f97316;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        .section-title {
            font-size: clamp(24px, 4vw, 36px);
            font-weight: 800;
            color: #111827;
            line-height: 1.2;
            margin-bottom: 16px;
        }
        .section-subtitle {
            font-size: 16px;
            color: #6b7280;
            max-width: 560px;
            margin: 0 auto 48px;
            line-height: 1.65;
            text-align: center;
        }

        /* ==============================
           TYPE SECTION
        ============================== */
        .type-section {
            background: #fff;
        }
        .type-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 16px;
            margin-top: 36px;
        }
        @media (max-width: 900px) { .type-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 500px) { .type-grid { grid-template-columns: repeat(2, 1fr); } }
        .type-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 24px 12px;
            border: 2px solid #f3f4f6;
            border-radius: 16px;
            text-decoration: none;
            transition: all 0.25s;
            background: #fafafa;
            cursor: pointer;
        }
        .type-card:hover {
            border-color: #f97316;
            background: #fff7ed;
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(249,115,22,0.15);
        }
        .type-icon {
            font-size: 32px;
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .type-card:hover .type-icon {
            background: #fff7ed;
        }
        .type-name {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }

        /* ==============================
           RECOMMENDED PROPERTIES
        ============================== */
        .recommend-section {
            background: #f9fafb;
        }
        .section-header-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .btn-outline {
            padding: 10px 22px;
            border: 2px solid #f97316;
            border-radius: 10px;
            color: #f97316;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-outline:hover {
            background: #f97316;
            color: #fff;
        }
        .property-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        @media (max-width: 1100px) { .property-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) { .property-grid { grid-template-columns: 1fr; } }
        .property-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 16px rgba(0,0,0,0.07);
            transition: all 0.3s;
        }
        .property-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.13);
        }
        .card-img-wrap {
            position: relative;
            overflow: hidden;
        }
        .card-img {
            width: 100%;
            height: 195px;
            object-fit: cover;
            transition: transform 0.4s;
        }
        .property-card:hover .card-img {
            transform: scale(1.05);
        }
        .card-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-jual { background: #f97316; color: #fff; }
        .badge-sewa { background: #3b82f6; color: #fff; }
        .card-fav {
            position: absolute;
            top: 10px;
            right: 12px;
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.9);
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .card-fav:hover { background: #fff; color: #ef4444; }
        .card-body { padding: 16px; }
        .card-price {
            font-size: 18px;
            font-weight: 800;
            color: #f97316;
            margin-bottom: 6px;
        }
        .card-title {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 6px;
            line-height: 1.4;
        }
        .card-location {
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 12px;
        }
        .card-meta {
            display: flex;
            gap: 12px;
            padding-top: 12px;
            border-top: 1px solid #f3f4f6;
        }
        .meta-item {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
        }

        /* ==============================
           SOLUTION SECTION
        ============================== */
        .solution-section {
            background: #fff;
        }
        .solution-inner {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 64px;
            align-items: center;
        }
        @media (max-width: 900px) {
            .solution-inner { grid-template-columns: 1fr; gap: 40px; }
        }
        .sol-img-wrap {
            position: relative;
            padding-bottom: 28px;
        }
        .sol-img {
            width: 100%;
            height: 460px;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        .sol-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #fff;
            border-radius: 16px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            max-width: calc(100% - 24px);
        }
        .sol-badge-icon { font-size: 28px; }
        .sol-badge-num {
            font-size: 20px;
            font-weight: 800;
            color: #f97316;
            line-height: 1;
        }
        .sol-badge-label {
            font-size: 12px;
            color: #9ca3af;
            font-weight: 500;
        }
        .solution-desc {
            font-size: 15px;
            color: #6b7280;
            line-height: 1.7;
            margin-bottom: 32px;
        }
        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .feature-item {
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }
        .feature-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .feature-icon-wrap.orange { background: #fff7ed; }
        .feature-icon-wrap.blue { background: #eff6ff; }
        .feature-icon-wrap.green { background: #f0fdf4; }
        .feature-icon-wrap.purple { background: #faf5ff; }
        .feature-title {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }
        .feature-desc {
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.55;
        }

        /* ==============================
           STATS SECTION
        ============================== */
        .stats-section {
            background: #1a1a2e;
            padding: 64px 24px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
            max-width: 1200px;
            margin: 0 auto;
        }
        @media (max-width: 700px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: clamp(32px, 5vw, 48px);
            font-weight: 900;
            color: #f97316;
            line-height: 1;
            margin-bottom: 8px;
        }
        .stat-label {
            font-size: 14px;
            color: rgba(255,255,255,0.65);
            font-weight: 500;
        }

        /* ==============================
           HOW IT WORKS
        ============================== */
        .how-section {
            background: #f9fafb;
        }
        .how-grid {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 48px;
        }
        @media (max-width: 800px) {
            .how-grid { flex-direction: column; }
            .how-connector { transform: rotate(90deg); }
        }
        .how-card {
            flex: 1;
            background: #fff;
            border-radius: 20px;
            padding: 32px 28px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.06);
            position: relative;
            transition: all 0.3s;
        }
        .how-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px rgba(249,115,22,0.12);
            border: 2px solid #fed7aa;
        }
        .how-step {
            font-size: 48px;
            font-weight: 900;
            color: #fed7aa;
            line-height: 1;
            margin-bottom: 12px;
        }
        .how-icon {
            font-size: 36px;
            margin-bottom: 16px;
        }
        .how-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 10px;
        }
        .how-desc {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.65;
        }
        .how-connector {
            font-size: 28px;
            color: #d1d5db;
            flex-shrink: 0;
        }

        /* ==============================
           TESTIMONIALS
        ============================== */
        .testimonial-section {
            background: #fff;
        }
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-top: 48px;
        }
        @media (max-width: 900px) { .testimonial-grid { grid-template-columns: 1fr; } }
        .testimonial-card {
            background: #f9fafb;
            border-radius: 20px;
            padding: 28px;
            border: 2px solid transparent;
            transition: all 0.3s;
        }
        .testimonial-card:hover {
            border-color: #fed7aa;
            box-shadow: 0 8px 32px rgba(249,115,22,0.1);
        }
        .testimonial-card.featured {
            background: #fff7ed;
            border-color: #fdba74;
            box-shadow: 0 8px 32px rgba(249,115,22,0.15);
        }
        .stars {
            font-size: 16px;
            margin-bottom: 16px;
        }
        .testimonial-text {
            font-size: 14px;
            color: #374151;
            line-height: 1.7;
            margin-bottom: 20px;
            font-style: italic;
        }
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .author-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fed7aa;
        }
        .author-name {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }
        .author-role {
            font-size: 12px;
            color: #9ca3af;
        }

        /* ==============================
           CTA SECTION
        ============================== */
        .cta-section {
            position: relative;
            padding: 100px 24px;
            overflow: hidden;
            text-align: center;
        }
        .cta-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .cta-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(26,26,46,0.92) 0%, rgba(26,26,46,0.80) 100%);
        }
        .cta-content {
            position: relative;
            z-index: 2;
        }
        .cta-title {
            font-size: clamp(28px, 5vw, 44px);
            font-weight: 800;
            color: #fff;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }
        .cta-subtitle {
            font-size: 16px;
            color: rgba(255,255,255,0.75);
            max-width: 500px;
            margin: 0 auto 36px;
            line-height: 1.65;
        }
        .btn-cta {
            display: inline-block;
            background: #f97316;
            color: #fff;
            padding: 16px 40px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.25s;
            box-shadow: 0 6px 24px rgba(249,115,22,0.45);
        }
        .btn-cta:hover {
            background: #ea6c0a;
            transform: translateY(-2px);
            box-shadow: 0 10px 32px rgba(249,115,22,0.5);
        }

        /* ==============================
           FOOTER
        ============================== */
        .footer {
            background: #111827;
            color: #fff;
        }
        .footer-inner {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
            gap: 40px;
            padding: 64px 24px;
            max-width: 1200px;
            margin: 0 auto;
        }
        @media (max-width: 1000px) {
            .footer-inner { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 600px) {
            .footer-inner { grid-template-columns: 1fr; }
        }
        .footer-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }
        .footer-logo-text {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
        }
        .footer-tagline {
            font-size: 14px;
            color: rgba(255,255,255,0.55);
            line-height: 1.65;
            margin-bottom: 24px;
        }
        .footer-socials {
            display: flex;
            gap: 10px;
        }
        .social-btn {
            width: 36px;
            height: 36px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .social-btn:hover {
            background: #f97316;
            border-color: #f97316;
            transform: translateY(-2px);
        }
        .footer-col-title {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 18px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .footer-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .footer-links a {
            font-size: 13px;
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-links a:hover { color: #f97316; }
        .footer-links li {
            font-size: 13px;
            color: rgba(255,255,255,0.55);
        }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.08);
            padding: 20px 24px;
        }
        .footer-bottom-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: rgba(255,255,255,0.4);
            flex-wrap: wrap;
            gap: 8px;
        }

        /* Scroll animations */
        .fade-in {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .fade-in.visible {
            opacity: 1;
            transform: none;
        }

        /* ==============================
           RESPONSIVE – TABLET (≤ 768px)
        ============================== */
        @media (max-width: 768px) {
            /* Navbar */
            .nav-links, .btn-nav-cta { display: none; }
            .nav-hamburger { display: flex; }

            /* Hero */
            .hero-content { padding: 100px 20px 60px; }
            .hero-subtitle { font-size: 15px; }

            /* Search box */
            .search-box { border-radius: 14px; }
            .search-fields { flex-direction: column; align-items: stretch; gap: 0; padding: 12px 16px; }
            .search-field { min-width: unset; padding: 10px 0 !important; border-bottom: 1px solid #f3f4f6; }
            .search-field:last-of-type { border-bottom: none; }
            .search-divider { display: none; }
            .btn-search { margin-top: 12px; border-radius: 10px; }

            /* Sections */
            .section { padding: 56px 20px; }
            .section-header-row { flex-direction: column; align-items: flex-start; }

            /* Solution */
            .sol-img { height: 280px; }
            .features-grid { grid-template-columns: 1fr; gap: 16px; }

            /* Stats */
            .stats-section { padding: 48px 20px; }

            /* Testimonial */
            .testimonial-grid { grid-template-columns: 1fr 1fr; }

            /* CTA */
            .cta-section { padding: 72px 20px; }
            .btn-cta { padding: 14px 28px; font-size: 15px; }
        }

        /* ==============================
           RESPONSIVE – MOBILE (≤ 480px)
        ============================== */
        @media (max-width: 480px) {
            /* Navbar */
            .navbar-inner { padding: 0 16px; }

            /* Hero */
            .hero-content { padding: 88px 16px 48px; }
            .hero-badge { font-size: 12px; padding: 5px 12px; }
            .hero-subtitle { font-size: 14px; margin-bottom: 28px; }

            /* Search */
            .search-tab { padding: 12px 18px; font-size: 13px; }

            /* Sections */
            .section { padding: 40px 16px; }
            .section-header-row .btn-outline { align-self: flex-start; }

            /* Type grid – already 2 cols but make padding tighter */
            .type-card { padding: 18px 8px; }
            .type-icon { font-size: 26px; width: 50px; height: 50px; }

            /* Property grid */
            .property-grid { grid-template-columns: 1fr; }

            /* Solution */
            .sol-img { height: 220px; }
            .sol-badge { padding: 10px 14px; gap: 8px; }
            .sol-badge-num { font-size: 16px; }
            .sol-badge-icon { font-size: 22px; }

            /* Stats */
            .stats-section { padding: 36px 16px; }
            .stats-grid { gap: 20px; }

            /* How it works */
            .how-card { padding: 24px 20px; }
            .how-step { font-size: 36px; }

            /* Testimonial */
            .testimonial-grid { grid-template-columns: 1fr; }

            /* CTA */
            .cta-section { padding: 56px 16px; }
            .cta-subtitle { font-size: 14px; }
            .btn-cta { display: block; text-align: center; padding: 14px 20px; }

            /* Footer */
            .footer-inner { padding: 40px 16px; }
            .footer-bottom { padding: 16px; }
            .footer-bottom-inner { flex-direction: column; text-align: center; gap: 4px; }
        }
    </style>
</head>
<body style="margin:0;font-family:'Instrument Sans',ui-sans-serif,system-ui,sans-serif;background:#fff;">

    <!-- ===== NAVBAR ===== -->
    <nav class="navbar" id="navbar">
        <div class="navbar-inner">
            <a href="#" class="nav-logo">
                <div class="nav-logo-icon">🏠</div>
                <span class="nav-logo-text">nihom</span>
            </a>
            <ul class="nav-links">
                <li><a href="#beranda" class="nav-link active">Beranda</a></li>
                <li><a href="#properti" class="nav-link">Properti</a></li>
                <li><a href="#tentang" class="nav-link">Tentang Kami</a></li>
                <li><a href="#kontak" class="nav-link">Kontak</a></li>
            </ul>
            <a href="#" class="btn-nav-cta">Daftar Gratis</a>
            <button class="nav-hamburger" id="hamburgerBtn" aria-label="Buka Menu" aria-expanded="false">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </nav>

    <!-- ===== MOBILE NAV DRAWER ===== -->
    <div class="mobile-nav" id="mobileNav">
        <a href="#beranda" class="mobile-nav-link active" onclick="closeMobileNav()">Beranda</a>
        <a href="#properti" class="mobile-nav-link" onclick="closeMobileNav()">Properti</a>
        <a href="#tentang" class="mobile-nav-link" onclick="closeMobileNav()">Tentang Kami</a>
        <a href="#kontak" class="mobile-nav-link" onclick="closeMobileNav()">Kontak</a>
        <a href="#" class="mobile-nav-cta">Daftar Gratis</a>
    </div>

    <!-- ===== HERO ===== -->
    <section class="hero-section" id="beranda">
        <img
            src="https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=1600&q=80&auto=format&fit=crop"
            alt="Rumah Mewah"
            class="hero-bg"
        />
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <p class="hero-badge">🏡 Platform Properti #1 Indonesia</p>
            <h1 class="hero-title">Temukan Properti<br/>Impian Anda</h1>
            <p class="hero-subtitle">Kami membantu Anda menemukan properti yang sempurna sesuai kebutuhan dan anggaran. Lebih dari 10.000 listing terpercaya tersedia.</p>

            <div class="search-box">
                <div class="search-tabs">
                    <button class="search-tab active" onclick="setTab(this,'Beli')">Beli</button>
                    <button class="search-tab" onclick="setTab(this,'Sewa')">Sewa</button>
                </div>
                <div class="search-fields">
                    <div class="search-field" style="padding: 0 12px;">
                        <label class="field-label">Lokasi</label>
                        <select class="field-select">
                            <option>Jakarta Selatan, Indonesia</option>
                            <option>Bali</option>
                            <option>Surabaya</option>
                            <option>Bandung</option>
                            <option>Yogyakarta</option>
                        </select>
                    </div>
                    <div class="search-divider"></div>
                    <div class="search-field" style="padding: 0 12px;">
                        <label class="field-label">Tipe Properti</label>
                        <select class="field-select">
                            <option>Semua Tipe</option>
                            <option>Rumah</option>
                            <option>Apartemen</option>
                            <option>Villa</option>
                            <option>Ruko</option>
                        </select>
                    </div>
                    <div class="search-divider"></div>
                    <div class="search-field" style="padding: 0 12px;">
                        <label class="field-label">Kisaran Harga</label>
                        <select class="field-select">
                            <option>Semua Harga</option>
                            <option>di bawah 500 Juta</option>
                            <option>500 Juta – 1 Miliar</option>
                            <option>1 – 3 Miliar</option>
                            <option>di atas 3 Miliar</option>
                        </select>
                    </div>
                    <button class="btn-search">🔍 Cari Properti</button>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== PROPERTY TYPES ===== -->
    <section class="section type-section fade-in" id="properti">
        <div class="container">
            <p class="section-tag">KATEGORI</p>
            <h2 class="section-title">Jelajahi Berdasarkan Tipe Properti</h2>
            <div class="type-grid">
                <a href="#" class="type-card">
                    <div class="type-icon">🏠</div>
                    <span class="type-name">Rumah</span>
                </a>
                <a href="#" class="type-card">
                    <div class="type-icon">🏢</div>
                    <span class="type-name">Apartemen</span>
                </a>
                <a href="#" class="type-card">
                    <div class="type-icon">🌿</div>
                    <span class="type-name">Tanah</span>
                </a>
                <a href="#" class="type-card">
                    <div class="type-icon">🏪</div>
                    <span class="type-name">Ruko</span>
                </a>
                <a href="#" class="type-card">
                    <div class="type-icon">🏖️</div>
                    <span class="type-name">Villa</span>
                </a>
                <a href="#" class="type-card">
                    <div class="type-icon">🏗️</div>
                    <span class="type-name">Kantor</span>
                </a>
            </div>
        </div>
    </section>

    <!-- ===== RECOMMENDED PROPERTIES ===== -->
    <section class="section recommend-section fade-in">
        <div class="container">
            <div class="section-header-row">
                <div>
                    <p class="section-tag">REKOMENDASI</p>
                    <h2 class="section-title" style="margin-bottom:0;">Rekomendasi Properti Terbaik Untuk Anda</h2>
                </div>
                <a href="#" class="btn-outline">Lihat Semua Properti →</a>
            </div>
            <div class="property-grid">

                <div class="property-card">
                    <div class="card-img-wrap">
                        <img src="https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=500&q=80&auto=format&fit=crop" alt="Rumah Modern" class="card-img"/>
                        <span class="card-badge badge-jual">Dijual</span>
                        <button class="card-fav" aria-label="Favorit">♡</button>
                    </div>
                    <div class="card-body">
                        <p class="card-price">Rp 1,2 M</p>
                        <h3 class="card-title">Rumah Modern 3 Kamar Tidur</h3>
                        <p class="card-location">📍 Kemang, Jakarta Selatan</p>
                        <div class="card-meta">
                            <span class="meta-item">🛏 3</span>
                            <span class="meta-item">🚿 2</span>
                            <span class="meta-item">📐 120 m²</span>
                        </div>
                    </div>
                </div>

                <div class="property-card">
                    <div class="card-img-wrap">
                        <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=500&q=80&auto=format&fit=crop" alt="Villa Mewah" class="card-img"/>
                        <span class="card-badge badge-jual">Dijual</span>
                        <button class="card-fav" aria-label="Favorit">♡</button>
                    </div>
                    <div class="card-body">
                        <p class="card-price">Rp 3,5 M</p>
                        <h3 class="card-title">Villa Mewah dengan Kolam Renang</h3>
                        <p class="card-location">📍 Ubud, Bali</p>
                        <div class="card-meta">
                            <span class="meta-item">🛏 4</span>
                            <span class="meta-item">🚿 3</span>
                            <span class="meta-item">📐 250 m²</span>
                        </div>
                    </div>
                </div>

                <div class="property-card">
                    <div class="card-img-wrap">
                        <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=500&q=80&auto=format&fit=crop" alt="Apartemen" class="card-img"/>
                        <span class="card-badge badge-sewa">Disewa</span>
                        <button class="card-fav" aria-label="Favorit">♡</button>
                    </div>
                    <div class="card-body">
                        <p class="card-price">Rp 8,5 Jt<span style="font-size:12px;font-weight:500;">/bln</span></p>
                        <h3 class="card-title">Apartemen Studio City View</h3>
                        <p class="card-location">📍 Sudirman, Jakarta Pusat</p>
                        <div class="card-meta">
                            <span class="meta-item">🛏 1</span>
                            <span class="meta-item">🚿 1</span>
                            <span class="meta-item">📐 42 m²</span>
                        </div>
                    </div>
                </div>

                <div class="property-card">
                    <div class="card-img-wrap">
                        <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=500&q=80&auto=format&fit=crop" alt="Rumah Cluster" class="card-img"/>
                        <span class="card-badge badge-jual">Dijual</span>
                        <button class="card-fav" aria-label="Favorit">♡</button>
                    </div>
                    <div class="card-body">
                        <p class="card-price">Rp 2,1 M</p>
                        <h3 class="card-title">Rumah Cluster Premium Baru</h3>
                        <p class="card-location">📍 BSD City, Tangerang</p>
                        <div class="card-meta">
                            <span class="meta-item">🛏 4</span>
                            <span class="meta-item">🚿 3</span>
                            <span class="meta-item">📐 180 m²</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ===== SOLUTION SECTION ===== -->
    <section class="section solution-section fade-in" id="tentang">
        <div class="container solution-inner">
            <div class="sol-img-wrap">
                <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=700&q=80&auto=format&fit=crop" alt="Pasangan Bahagia" class="sol-img"/>
                <div class="sol-badge">
                    <span class="sol-badge-icon">✅</span>
                    <div>
                        <p class="sol-badge-num">10.000+</p>
                        <p class="sol-badge-label">Pelanggan Puas</p>
                    </div>
                </div>
            </div>
            <div>
                <p class="section-tag">KENAPA NIHOM?</p>
                <h2 class="section-title">Solusi Properti Terbaik<br/>Tanpa Ribet</h2>
                <p class="solution-desc">Nihom hadir untuk memudahkan Anda dalam mencari, membeli, atau menyewa properti impian. Dengan teknologi terkini dan agen berpengalaman, kami siap membantu setiap langkah.</p>
                <div class="features-grid">
                    <div class="feature-item">
                        <div class="feature-icon-wrap orange">🔍</div>
                        <div>
                            <h4 class="feature-title">Pencarian Mudah</h4>
                            <p class="feature-desc">Filter canggih untuk menemukan properti sesuai kebutuhan dengan cepat.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon-wrap blue">💰</div>
                        <div>
                            <h4 class="feature-title">Harga Transparan</h4>
                            <p class="feature-desc">Tidak ada biaya tersembunyi. Semua harga disajikan secara jelas.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon-wrap green">🤝</div>
                        <div>
                            <h4 class="feature-title">Agen Terpercaya</h4>
                            <p class="feature-desc">Jaringan agen terverifikasi dan berpengalaman siap mendampingi Anda.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon-wrap purple">⚡</div>
                        <div>
                            <h4 class="feature-title">Proses Cepat</h4>
                            <p class="feature-desc">Dari pencarian hingga serah terima kunci, proses efisien dan profesional.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== STATS SECTION ===== -->
    <section class="stats-section fade-in">
        <div class="stats-grid">
            <div class="stat-item">
                <p class="stat-number" id="stat1">10,000+</p>
                <p class="stat-label">Properti Terdaftar</p>
            </div>
            <div class="stat-item">
                <p class="stat-number" id="stat2">5,000+</p>
                <p class="stat-label">Transaksi Berhasil</p>
            </div>
            <div class="stat-item">
                <p class="stat-number" id="stat3">500+</p>
                <p class="stat-label">Agen Terpercaya</p>
            </div>
            <div class="stat-item">
                <p class="stat-number" id="stat4">50+</p>
                <p class="stat-label">Kota Terjangkau</p>
            </div>
        </div>
    </section>

    <!-- ===== HOW IT WORKS ===== -->
    <section class="section how-section fade-in">
        <div class="container" style="text-align:center;">
            <p class="section-tag" style="text-align:center;">CARA KERJA</p>
            <h2 class="section-title" style="text-align:center;">Bagaimana nihom Membantu Anda?</h2>
            <p class="section-subtitle">Tiga langkah mudah untuk menemukan properti impian Anda bersama kami.</p>
            <div class="how-grid">
                <div class="how-card">
                    <div class="how-step">01</div>
                    <div class="how-icon">🔎</div>
                    <h3 class="how-title">Cari Properti</h3>
                    <p class="how-desc">Gunakan fitur pencarian canggih kami untuk menemukan properti sesuai lokasi, tipe, dan anggaran yang Anda inginkan.</p>
                </div>
                <div class="how-connector">→</div>
                <div class="how-card">
                    <div class="how-step">02</div>
                    <div class="how-icon">📞</div>
                    <h3 class="how-title">Hubungi Agen</h3>
                    <p class="how-desc">Langsung terhubung dengan agen properti terverifikasi untuk mendapatkan informasi lengkap dan jadwalkan kunjungan.</p>
                </div>
                <div class="how-connector">→</div>
                <div class="how-card">
                    <div class="how-step">03</div>
                    <div class="how-icon">🎉</div>
                    <h3 class="how-title">Selesaikan Transaksi</h3>
                    <p class="how-desc">Kami membantu proses administrasi hingga selesai, mulai dari penawaran hingga serah terima kunci properti.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== TESTIMONIALS ===== -->
    <section class="section testimonial-section fade-in">
        <div class="container">
            <p class="section-tag" style="text-align:center;">TESTIMONI</p>
            <h2 class="section-title" style="text-align:center;">Apa Kata Mereka Tentang Kami?</h2>
            <div class="testimonial-grid">

                <div class="testimonial-card">
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                    <p class="testimonial-text">"Luar biasa! Saya berhasil menemukan rumah impian di Jakarta hanya dalam 2 minggu menggunakan nihom. Prosesnya sangat mudah dan agennya sangat membantu."</p>
                    <div class="testimonial-author">
                        <img src="https://i.pravatar.cc/60?img=1" alt="Budi Santoso" class="author-avatar"/>
                        <div>
                            <p class="author-name">Budi Santoso</p>
                            <p class="author-role">Pembeli Properti</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card featured">
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                    <p class="testimonial-text">"Platform terbaik untuk cari properti di Indonesia! Informasinya lengkap, harga transparan, dan agen-agennya profesional. Sangat direkomendasikan!"</p>
                    <div class="testimonial-author">
                        <img src="https://i.pravatar.cc/60?img=5" alt="Siti Rahayu" class="author-avatar"/>
                        <div>
                            <p class="author-name">Siti Rahayu</p>
                            <p class="author-role">Penyewa Apartemen</p>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="stars">⭐⭐⭐⭐⭐</div>
                    <p class="testimonial-text">"Sudah 3 kali transaksi properti melalui nihom dan selalu puas. Layanannya profesional, cepat, dan terpercaya. Nihom pilihan utama saya!"</p>
                    <div class="testimonial-author">
                        <img src="https://i.pravatar.cc/60?img=11" alt="Anton Budiman" class="author-avatar"/>
                        <div>
                            <p class="author-name">Anton Budiman</p>
                            <p class="author-role">Investor Properti</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ===== CTA SECTION ===== -->
    <section class="cta-section fade-in">
        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1400&q=80&auto=format&fit=crop" alt="Rumah" class="cta-bg"/>
        <div class="cta-overlay"></div>
        <div class="cta-content">
            <h2 class="cta-title">Siap Menemukan Rumah Impian Anda?</h2>
            <p class="cta-subtitle">Bergabunglah dengan lebih dari 50.000 pengguna yang telah menemukan properti impian mereka bersama nihom.</p>
            <a href="#" class="btn-cta">Mulai Sekarang →</a>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer class="footer" id="kontak">
        <div class="footer-inner">
            <div>
                <div class="footer-logo">
                    <div class="nav-logo-icon">🏠</div>
                    <span class="footer-logo-text">nihom</span>
                </div>
                <p class="footer-tagline">Platform properti terpercaya untuk menemukan rumah impian Anda di seluruh Indonesia.</p>
                <div class="footer-socials">
                    <a href="#" class="social-btn" aria-label="Instagram">📷</a>
                    <a href="#" class="social-btn" aria-label="Facebook">📘</a>
                    <a href="#" class="social-btn" aria-label="Twitter">🐦</a>
                    <a href="#" class="social-btn" aria-label="YouTube">▶️</a>
                </div>
            </div>

            <div>
                <h4 class="footer-col-title">Layanan Kami</h4>
                <ul class="footer-links">
                    <li><a href="#">Beli Properti</a></li>
                    <li><a href="#">Sewa Properti</a></li>
                    <li><a href="#">Jual Properti</a></li>
                    <li><a href="#">KPR Rumah</a></li>
                    <li><a href="#">Konsultasi</a></li>
                </ul>
            </div>

            <div>
                <h4 class="footer-col-title">Tipe Properti</h4>
                <ul class="footer-links">
                    <li><a href="#">Rumah</a></li>
                    <li><a href="#">Apartemen</a></li>
                    <li><a href="#">Villa</a></li>
                    <li><a href="#">Ruko</a></li>
                    <li><a href="#">Tanah & Kavling</a></li>
                </ul>
            </div>

            <div>
                <h4 class="footer-col-title">Informasi</h4>
                <ul class="footer-links">
                    <li><a href="#">Tentang Nihom</a></li>
                    <li><a href="#">Blog Properti</a></li>
                    <li><a href="#">Karir</a></li>
                    <li><a href="#">Syarat & Ketentuan</a></li>
                    <li><a href="#">Kebijakan Privasi</a></li>
                </ul>
            </div>

            <div>
                <h4 class="footer-col-title">Hubungi Kami</h4>
                <ul class="footer-links">
                    <li>📍 Jl. Sudirman No. 123, Jakarta Pusat</li>
                    <li>📞 +62 21 1234 5678</li>
                    <li>✉️ hello@nihom.id</li>
                    <li>🕐 Sen – Jum: 08.00 – 17.00</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="footer-bottom-inner">
                <p>© 2026 Nihom. Hak cipta dilindungi.</p>
                <p>Dibuat dengan ❤️ di Indonesia</p>
            </div>
        </div>
    </footer>

    <script>
        // ——— Navbar scroll effect ———
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 60) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // ——— Hamburger menu toggle ———
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const mobileNav = document.getElementById('mobileNav');

        function closeMobileNav() {
            hamburgerBtn.classList.remove('open');
            mobileNav.classList.remove('open');
            hamburgerBtn.setAttribute('aria-expanded', 'false');
        }

        hamburgerBtn.addEventListener('click', () => {
            const isOpen = mobileNav.classList.toggle('open');
            hamburgerBtn.classList.toggle('open', isOpen);
            hamburgerBtn.setAttribute('aria-expanded', String(isOpen));
        });

        // Close mobile nav when clicking outside
        document.addEventListener('click', (e) => {
            if (!navbar.contains(e.target) && !mobileNav.contains(e.target)) {
                closeMobileNav();
            }
        });

        // ——— Search tab switch ———
        function setTab(el, mode) {
            document.querySelectorAll('.search-tab').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
        }

        // ——— Scroll animations ———
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.12 });

        document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));

        // ——— Smooth scroll for nav links ———
        document.querySelectorAll('a[href^="#"]').forEach(link => {
            link.addEventListener('click', (e) => {
                const target = document.querySelector(link.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
