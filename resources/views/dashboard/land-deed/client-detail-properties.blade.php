@extends('layouts.app')
@section('page-title', 'Detail Properti Klien')
@section('content')
    @php
        $backUrl = route('land-deed.client-properties');
        $calendarUrl = route('land-deed.index');
        $calendarLabel = 'Kalender antrian';
        $historyTitle = 'Riwayat survei klien';
        $historySubtitle = 'Seluruh riwayat kunjungan klien untuk properti ini';
        $showDpPaymentAction = false;
        $showBuyerInHistory = true;
        $stageThreeUrl = route('land-deed.bpn-legal-check', ['property' => $property->uuid]);
        $breadcrumbs = [
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Dashboard Antrian', 'url' => route('land-deed.index')],
            ['label' => 'Properti Klien', 'url' => route('land-deed.client-properties')],
        ];
    @endphp

    @include('dashboard.partials.property-detail-content')
@endsection
