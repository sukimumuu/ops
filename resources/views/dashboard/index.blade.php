@extends('layouts.app')
@section('page-title', 'Dashboard')
@section('content')
    @role('User')
        @include('dashboard.user.index')
    @endrole
    @role('PPAT')
        {{-- PPAT is automatically redirected to land-deed.index --}}
    @endrole
    @role('Admin')
        @include('dashboard.admin.index')
    @endrole
    @role('Superadmin')
        @include('dashboard.superadmin.index')
    @endrole
@endsection
