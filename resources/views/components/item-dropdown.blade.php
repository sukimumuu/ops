@props([
    'href' => '#',
    'danger' => false,
])

<li role="none">
    <a href="{{ $href }}" role="menuitem" @click="$dispatch('dropdown-close')"
        {{ $attributes->merge([
            'class' =>
                'flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold transition
                               focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/30' .
                ($danger
                    ? ' text-red-600 hover:bg-red-50 focus-visible:bg-red-50'
                    : ' text-secondary hover:bg-primary-soft hover:text-primary focus-visible:bg-primary-soft'),
        ]) }}>
        {{ $slot }}
    </a>
</li>
