@props([
    'label'       => null,
    'required'    => false,
    'error'       => null,
    'hint'        => null,
])

<div>
    @if($label)
        <x-form-label-dashboard :value="$label" :required="$required" />
    @endif

    <input {{ $attributes->merge([
        'type'  => 'text',
        'class' => 'block w-full rounded-lg border bg-white px-4 py-2.5 text-sm font-medium
                     text-secondary placeholder-slate-400 shadow-sm outline-none transition
                     focus:border-primary focus:ring-2 focus:ring-primary/20
                     disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400'
                     . ($error ? ' border-red-400 focus:border-red-500 focus:ring-red-200'
                               : ' border-slate-300'),
    ]) }}>

    @if($hint && !$error)
        <p class="mt-1.5 text-xs text-slate-400">{{ $hint }}</p>
    @endif

    @if($error)
        <p class="mt-1.5 text-xs font-semibold text-red-600">{{ $error }}</p>
    @endif
</div>