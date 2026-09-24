<header class="sticky top-0 z-30 flex items-center gap-4 h-16 px-4 sm:px-6
                bg-white border-b border-slate-200 shadow-sm">

    <!-- Mobile menu button -->
    <button @click="sidebarOpen = true"
            class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-secondary hover:bg-slate-100">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- Page title (optional yield) -->
    <h1 class="font-heading text-lg font-bold text-secondary">
        @yield('page-title', 'Dashboard')
    </h1>

    <div class="flex-1"></div>

    <!-- Search (desktop) -->
    <div class="hidden md:block relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </span>
        <input type="text" placeholder="Search…"
                class="w-64 pl-10 pr-4 py-2 rounded-lg bg-slate-100 border border-transparent
                        text-sm placeholder-slate-400 text-secondary
                        focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none
                        transition">
    </div>

    <!-- Notifications -->
    <button class="relative p-2 rounded-lg text-slate-500 hover:text-secondary hover:bg-slate-100">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-primary"></span>
    </button>

    <!-- User dropdown -->
    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open"
                class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 transition">
            <span class="flex items-center justify-center w-9 h-9 rounded-full bg-secondary text-white font-heading font-bold text-sm">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
            </span>
            <svg class="hidden sm:block w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="open"
                x-cloak
                @click.outside="open = false"
                x-transition.origin.top.right
                class="absolute right-0 mt-2 w-48 rounded-xl bg-white border border-slate-200 shadow-lg py-1">
            <a href="#" class="block px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-primary-soft hover:text-primary">
                Profile
            </a>
            <a href="#" class="block px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-primary-soft hover:text-primary">
                Settings
            </a>
            <hr class="my-1 border-slate-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">
                    Log Out
                </button>
            </form>
        </div>
    </div>
</header>