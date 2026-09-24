@props([
    'label'       => null,
    'required'    => false,
    'error'       => null,
])

<div>
    @if($label)
        <x-form-label-dashboard :value="$label" :required="$required" />
    @endif

    <select {{ $attributes->merge([
        'class' => 'block w-full rounded-lg border bg-white px-4 py-2.5 text-sm font-medium
                     text-secondary shadow-sm outline-none transition
                     focus:border-primary focus:ring-2 focus:ring-primary/20
                     disabled:cursor-not-allowed disabled:bg-slate-50'
                     . ($error ? ' border-red-400' : ' border-slate-300'),
    ]) }}>
        {{ $slot }}
    </select>

    @if($error)
        <p class="mt-1.5 text-xs font-semibold text-red-600">{{ $error }}</p>
    @endif
</div>