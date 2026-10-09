<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dashboard\AdminController;
use App\Http\Controllers\Dashboard\BuyerController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\LandDeedController;
use App\Http\Controllers\Dashboard\SellerController;
use App\Http\Controllers\Dashboard\SuperadminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingWizardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'home'])->name('home');
Route::get('/kalkulator-pajak', [HomeController::class, 'taxCalculator'])->name('tax-calculator');
Route::get('/detail-properti', [HomeController::class, 'propertyDetails'])->name('property-details');
Route::get('/properti', [HomeController::class, 'property'])->name('properties');
Route::get('/tentang', [HomeController::class, 'about'])->name('about');
Route::get('/kontak', [HomeController::class, 'contact'])->name('contact');

Route::get('/masuk', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/masuk', [AuthController::class, 'login']);
Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');
Route::post('/daftar', [AuthController::class, 'register'])->name('register');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [DashboardController::class, 'profile'])->name('profile');
    Route::get('/notifikasi', [DashboardController::class, 'detailNotif'])->name('detail-notif');

    Route::name('seller.')->group(function () {
        Route::get('/properti-saya', [SellerController::class, 'myProperties'])->name('my-properties');
        Route::get('/permintaan-survei', [SellerController::class, 'requestSurvey'])->name('request-survey');
        Route::get('/transaksi-penjualan', [SellerController::class, 'sellingTransaction'])->name('selling-transaction');
        Route::get('/rekening-pencairan', [SellerController::class, 'disbursementAccount'])->name('disbursement-account');
    });
    Route::prefix('listing')->name('listing.')->group(function () {
        Route::get('/buat', [ListingWizardController::class, 'create'])->name('create');
        Route::post('/langkah-1', [ListingWizardController::class, 'storeStepOne'])->name('step-one');
        Route::post('/langkah-2', [ListingWizardController::class, 'storeStepTwo'])->name('step-two');
        Route::get('/tinjau-ulang', [ListingWizardController::class, 'review'])->name('review');
        Route::post('/buat', [ListingWizardController::class, 'submit'])->name('submit');
        Route::get('/photo-url/{propertyPhoto}', [ListingWizardController::class, 'signedPhotoUrl'])->name('photo-url');
    });

    Route::name('buyer.')->group(function () {
        Route::get('/properti-tersimpan', [BuyerController::class, 'savedProperties'])->name('saved-properties');
        Route::get('/jadwal-survei', [BuyerController::class, 'surveySchedule'])->name('survey-schedule');
        Route::get('/transaksi-escrow', [BuyerController::class, 'transactionEscrow'])->name('transaction-escrow');
    });

    Route::name('land-deed.')->group(function () {
        Route::get('/pengecekan-bpn', [LandDeedController::class, 'bpnCheck'])->name('bpn-check');
        Route::get('/penandatanganan-ajb', [LandDeedController::class, 'ajbSigning'])->name('ajb-signing');
        Route::get('/manajemen-akta', [LandDeedController::class, 'deedManagement'])->name('deed-management');
        Route::get('/validasi-pajak', [LandDeedController::class, 'taxValidation'])->name('tax-validation');
        Route::get('/resi-balik-nama', [LandDeedController::class, 'transferForOwnership'])->name('transfer-for-ownership');
    });

    Route::name('superadmin.')->group(function () {
        Route::get('/manajemen-user', [SuperadminController::class, 'userManagement'])->name('user-management');
        Route::get('/transaksi-escrow', [SuperadminController::class, 'transactionAndEscrow'])->name('transaction-and-escrow');
        Route::get('/master-data-properti', [SuperadminController::class, 'masterDataProperty'])->name('master-data-property');
        Route::get('/konfigurasi-sistem', [SuperadminController::class, 'configurationSystem'])->name('configuration-system');
        Route::get('/audit-log', [SuperadminController::class, 'auditLog'])->name('audit-log');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/manajemen-user', [AdminController::class, 'userManagement'])->name('user-management');
        Route::get('/transaksi-escrow', [AdminController::class, 'transactionAndEscrow'])->name('transaction-and-escrow');
        Route::get('/master-data-properti', [AdminController::class, 'masterDataProperty'])->name('master-data-property');
        Route::get('/audit-log', [AdminController::class, 'auditLog'])->name('audit-log');
    });
});
