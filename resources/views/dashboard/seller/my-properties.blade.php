@extends('layouts.app')
@section('page-title', 'Properti Saya')
@section('content')
    <x-card-dashboard title="Properti Saya"
            subtitle="Informasi tentang properti yang saya iklankan">
        <x-slot name="actions">
            <x-button-dashboard variant="primary" size="sm">
                <a href="{{ route('listing.create') }}">Tambah Properti Baru</a> 
            </x-button-dashboard>
        </x-slot>
        
    </x-card-dashboard>
@endsection