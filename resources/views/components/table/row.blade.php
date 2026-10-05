@props([
    'striped' => false,
    'hover'   => true,
])

<tr {{ $attributes->merge([
    'class' => ($striped ? 'odd:bg-white even:bg-slate-50/70 ' : '')
             . ($hover ? 'hover:bg-primary-soft/60 transition' : '')
             . ' border-b border-slate-100 last:border-0'
]) }}>
    {{ $slot }}
</tr>