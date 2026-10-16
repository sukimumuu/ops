@props([
    'type' => 'info', // 'info' or 'warning'
    'message' => '',
])

@php
    $classes = $type === 'warning'
        ? 'bg-rose-50/90 border border-rose-200 text-rose-800'
        : 'bg-blue-50/80 border border-blue-200 text-blue-900';
    $iconClass = $type === 'warning' ? 'text-rose-600' : 'text-blue-600';
@endphp

<div class="rounded-lg p-3.5 text-xs sm:text-sm flex items-start gap-2.5 leading-relaxed {{ $classes }} mb-6 shadow-xs">
    @if($type === 'warning')
        <svg class="size-4 shrink-0 mt-0.5 {{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
    @else
        <svg class="size-4 shrink-0 mt-0.5 {{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
        </svg>
    @endif
    <div>
        {{ $slot->isNotEmpty() ? $slot : $message }}
    </div>
</div>
