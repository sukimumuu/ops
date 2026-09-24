@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block mb-1.5 text-sm font-bold text-secondary']) }}>
    {{ $value ?? $slot }}
    @if($required)
        <span class="text-primary">*</span>
    @endif
</label>