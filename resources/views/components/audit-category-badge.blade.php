@props(['category'])

@php
    $style = match ($category) {
        \App\Models\AuditLog::CATEGORY_AUTHENTICATION => 'bg-slate-100 text-slate-700 ring-slate-200',
        \App\Models\AuditLog::CATEGORY_TRANSACTION => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        \App\Models\AuditLog::CATEGORY_SCHEDULING => 'bg-sky-50 text-sky-700 ring-sky-200',
        \App\Models\AuditLog::CATEGORY_MODERATION => 'bg-amber-50 text-amber-800 ring-amber-200',
        \App\Models\AuditLog::CATEGORY_ADMINISTRATION => 'bg-violet-50 text-violet-700 ring-violet-200',
        \App\Models\AuditLog::CATEGORY_SYSTEM => 'bg-rose-50 text-rose-700 ring-rose-200',
        default => 'bg-slate-50 text-slate-600 ring-slate-200',
    };
@endphp

<span
    {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset', $style]) }}>
    {{ \App\Models\AuditLog::categoryLabel($category) }}
</span>
