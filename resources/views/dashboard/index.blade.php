@extends('layouts.app')
@section('page-title', 'Dashboard')
@section('content')
    @role('Buyer')
        @include('dashboard.buyer.index')
    @endrole
    @role('Seller')
        @include('dashboard.seller.index')
    @endrole
    @role('PPAT')
        @include('dashboard.land-deed.index')
    @endrole
    @role('Superadmin')
        @include('dashboard.superadmin.index')
    @endrole
@endsection
