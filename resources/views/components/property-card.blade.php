@props(['image', 'tag', 'price', 'title', 'location', 'specs'])

<div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-xl transition-shadow duration-300 group cursor-pointer">
    <div class="relative h-56 overflow-hidden">
        <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        
        <div class="absolute top-4 left-4">
            <span class="bg-white text-primary font-bold text-[10px] uppercase tracking-wider py-1.5 px-3 rounded-full shadow-sm">
                {{ $tag }}
            </span>
        </div>
        
        <button class="absolute top-4 right-4 w-8 h-8 bg-white rounded-full flex items-center justify-center text-gray-400 hover:text-primary shadow-sm transition">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        </button>
    </div>
    
    <div class="p-5">
        <h3 class="text-primary font-extrabold text-xl mb-1">{{ $price }}</h3>
        <h4 class="text-secondary font-bold text-lg mb-1">{{ $title }}</h4>
        <p class="text-gray-500 text-xs mb-4">{{ $location }}</p>
        
        <div class="text-gray-600 text-xs font-semibold flex items-center gap-2">
            {{ $specs }}
        </div>
    </div>
</div>