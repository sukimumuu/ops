{{--
    Cloud upload icon (open-bottom cloud + centred upward arrow).

    Geometry is traced from the reference artwork: a central dome flanked by two
    side lobes, a flat open bottom, and a mitered arrow whose shaft drops below
    the cloud. Drawn as a monoline stroke so it stays crisp at small sizes.

    The stroke inherits `currentColor`, so place it inside a container that uses
    the Mihom primary colour (#FC4907) — e.g. the wizard dropzone.
--}}

@props(['class' => 'h-10 w-10'])

<svg {{ $attributes->merge(['class' => $class, 'aria-hidden' => 'true', 'focusable' => 'false']) }}
    viewBox="0 0 256 256" fill="none" stroke="currentColor" stroke-width="8" stroke-linecap="round"
    stroke-linejoin="round">
    <path
        d="M106 162H96.5A34.5 34.5 0 0 1 62 127.5A34.5 34.5 0 0 1 93.3 93.3A38.3 38.3 0 0 1 162.7 93.3A34.5 34.5 0 0 1 193.5 127.5A34.5 34.5 0 0 1 159.5 162H150" />
    <path stroke-linejoin="miter" stroke-miterlimit="10"
        d="M110.4 131.6L128 107.5L145.6 131.6M128 107.5V185" />
</svg>
