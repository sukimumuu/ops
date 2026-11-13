@extends('layouts.app')
@section('page-title', 'Audit Log')
@section('content')
    <div class="space-y-6">
        <header class="flex flex-col gap-2 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-primary">Administrator</p>
                <h1 class="mt-1 font-heading text-2xl font-bold text-secondary">Audit Log</h1>
                <p class="mt-1 text-sm text-slate-500">Riwayat autentikasi, transaksi, penjadwalan, dan moderasi
                    properti.</p>
            </div>
            <p class="text-sm text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</p>
        </header>

        @include('dashboard.partials.audit-log-summary')

        <x-card-dashboard title="Riwayat Aktivitas"
            subtitle="Jejak audit bersifat permanen dan hanya dapat dibaca oleh administrator.">
            <div class="space-y-4">
                <x-search-filter placeholder="Cari deskripsi, aktor, atau subjek...">
                    <div>
                        <label for="category-filter"
                            class="mb-1 block text-xs font-semibold text-slate-600">Kategori</label>
                        <select id="category-filter" name="category"
                            class="min-w-40 rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['category'] ?? null) === $value)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="event-filter" class="mb-1 block text-xs font-semibold text-slate-600">Peristiwa</label>
                        <select id="event-filter" name="event"
                            class="min-w-48 rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="">Semua peristiwa</option>
                            @foreach ($events as $event)
                                <option value="{{ $event }}" @selected(($filters['event'] ?? null) === $event)>
                                    {{ \App\Models\AuditLog::eventLabelFor($event) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="from-filter" class="mb-1 block text-xs font-semibold text-slate-600">Dari
                            tanggal</label>
                        <input id="from-filter" type="date" name="from" value="{{ $filters['from'] ?? '' }}"
                            class="rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                    <div>
                        <label for="to-filter" class="mb-1 block text-xs font-semibold text-slate-600">Sampai
                            tanggal</label>
                        <input id="to-filter" type="date" name="to" value="{{ $filters['to'] ?? '' }}"
                            class="rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                </x-search-filter>

                <x-audit-log-table :logs="$logs" />

                <x-table.pagination :paginator="$logs" />
            </div>
        </x-card-dashboard>
    </div>
@endsection
