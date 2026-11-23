@extends('layouts.app')
@section('page-title', 'Detail Properti Master Data')
@section('content')
    @php
        $backUrl = route('admin.master-data-property');
        $calendarUrl = route('admin.transaction-and-escrow');
        $calendarLabel = 'Transaksi & escrow';
        $historyTitle = 'Riwayat survei properti';
        $historySubtitle = 'Seluruh pengajuan dan hasil survei untuk properti ini';
        $showDpPaymentAction = false;
        $showEscrowPaymentAction = false;
        $showBuyerInHistory = true;
        $stageThreeUrl = null;
        $canManagePpatMeeting = false;
        $canManageAjbMeeting = false;
        $pendingPpatMeetingRequests = collect();
        $breadcrumbs = [
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Master Data Properti', 'url' => route('admin.master-data-property')],
        ];
    @endphp

    @include('dashboard.partials.property-detail-content')
@endsection
