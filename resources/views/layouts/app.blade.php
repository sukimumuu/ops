<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Dashboard'))</title>

    <!-- Google Fonts: Outfit (headings) + Manrope (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js for interactive components (sidebar toggle, dropdowns) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tailwind config: brand palette -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#FC4907',
                            hover:   '#E04006',
                            soft:    '#FFF1EB',
                        },
                        secondary: '#2C3E50',
                    },
                    fontFamily: {
                        heading: ['Outfit', 'sans-serif'],
                        body:    ['Manrope', 'sans-serif'],
                    },
                },
            },
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="h-full font-body bg-white text-slate-700 antialiased"
      x-data="{ sidebarOpen: false }">

<!-- ============ SIDEBAR (mobile overlay backdrop) ============ -->
<div x-show="sidebarOpen"
     x-cloak
     x-transition.opacity
     @click="sidebarOpen = false"
     class="fixed inset-0 z-40 bg-secondary/50 lg:hidden"></div>

<!-- ============ SIDEBAR ============ -->
@include('layouts.partials.sidebar')

<!-- ============ MAIN WRAPPER ============ -->
<div class="flex flex-col min-h-full lg:pl-64 transition-[padding] duration-300">

    <!-- Top navbar -->
    @include('layouts.partials.navbar')

    <!-- ============ MAIN CONTENT ============ -->
    <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 bg-white">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    @include('layouts.partials.footer')
</div>

</body>
</html>