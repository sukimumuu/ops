@props([
    'striped'  => true,
    'hover'    => true,
])

<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm']) }}>
    <table class="w-full text-sm text-left">
        <thead>
            <tr class="border-b border-slate-200 bg-slate-50">
                {{ $head }}
            </tr>
        </thead>
        <tbody class="font-medium text-secondary">
            {{ $slot }}
        </tbody>
    </table>
</div>