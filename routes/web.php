<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingWizardController;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'home'])->name('home');
Route::get('/kalkulator-pajak', [HomeController::class, 'taxCalculator'])->name('tax-calculator');
Route::get('/detail-properti', [HomeController::class, 'propertyDetails'])->name('property-details');
Route::get('/properti', [HomeController::class, 'property'])->name('properties');

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
});
