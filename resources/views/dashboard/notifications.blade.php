@extends('layouts.app')
@section('page-title', 'Notifikasi')
@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <header class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Pusat notifikasi</p>
                <h1 class="mt-1 font-heading text-2xl font-bold tracking-tight text-secondary">Semua notifikasi</h1>
            </div>
            <span class="rounded-full bg-orange-50 px-3 py-1 text-sm font-semibold text-primary">
                {{ auth()->user()->unreadNotifications()->count() }} belum dibaca
            </span>
        </header>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @forelse ($notifications as $notification)
                <a href="{{ $notification->data['url'] ?? route('dashboard') }}"
                    @if (is_null($notification->read_at)) data-notification-read="{{ $notification->id }}" @endif
                    class="flex items-start justify-between gap-4 border-b border-slate-100 p-5 transition last:border-b-0 hover:bg-slate-50 {{ is_null($notification->read_at) ? 'bg-orange-50/20' : '' }}">
                    <div class="min-w-0">
                        <h2 class="font-bold text-slate-800">{{ $notification->data['title'] ?? 'Notifikasi' }}</h2>
                        <p class="mt-1 text-sm text-slate-600">{{ $notification->data['message'] ?? '' }}</p>
                        <time class="mt-2 block text-xs text-slate-400" datetime="{{ $notification->created_at->toIso8601String() }}">
                            {{ $notification->created_at->locale('id')->translatedFormat('d M Y, H:i') }}
                        </time>
                    </div>
                    @if (is_null($notification->read_at))
                        <span class="mt-2 size-2.5 shrink-0 rounded-full bg-primary" aria-label="Belum dibaca"></span>
                    @endif
                </a>
            @empty
                <p class="px-6 py-12 text-center text-sm text-slate-500">Belum ada notifikasi.</p>
            @endforelse
        </div>

        {{ $notifications->links() }}
    </div>
@endsection
