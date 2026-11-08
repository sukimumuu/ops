@extends('layouts.app')
@section('page-title', 'Detail Properti Dibeli')
@section('content')
    @php
        $backUrl = route('buyer.bought-properties');
        $calendarUrl = route('buyer.survey-schedule');
        $calendarLabel = 'Kalender survei';
        $historyTitle = 'Riwayat survei saya';
        $historySubtitle = 'Seluruh riwayat kunjungan yang Anda jadwalkan untuk properti ini';
        $showDpPaymentAction = true;
        $showBuyerInHistory = false;
        $breadcrumbs = [
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Properti Dibeli', 'url' => route('buyer.bought-properties')],
        ];
    @endphp

    @include('dashboard.partials.property-detail-content')
@endsection
