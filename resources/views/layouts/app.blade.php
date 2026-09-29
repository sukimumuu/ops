<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Dashboard')) - @yield('page-title')</title>

    @vite(['resources/css/app.css', 'resources/css/welcome.css', 'resources/js/app.js'])

    <!-- Alpine.js for interactive components (sidebar toggle, dropdowns) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
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
<div class="flex flex-col min-h-full">

    <!-- Top navbar -->
    @include('layouts.partials.navbar')

    <!-- ============ MAIN CONTENT ============ -->
    <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 bg-white">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    @include('layouts.partials.footer')
</div>

@stack('scripts')
</body>
</html>