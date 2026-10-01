<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ListingWizardController;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');
Route::get('/properti', [PropertyController::class, 'index'])->name('properti');
Route::get('/properti/{property}', [PropertyController::class, 'show'])->name('properti.show');
Route::get('/kalkulator-pajak', function () {
    return view('kalkulator-pajak');
})->name('kalkulator-pajak');

Route::get('/masuk', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/masuk', [AuthController::class, 'login']);
Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');
Route::post('/daftar', [AuthController::class, 'register'])->name('register');
Route::get('/detail-properti', [DashboardController::class, 'detailProperty'])->name('detail-property');   
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/properti-saya', [DashboardController::class, 'myProperties'])->name('my-properties');
    // Listing Wizard (Progressive Listing)
    Route::prefix('listing')->name('listing.')->group(function () {
        Route::get('/buat', [ListingWizardController::class, 'create'])->name('create');
        Route::post('/step-1', [ListingWizardController::class, 'storeStepOne'])->name('step-one');
        Route::post('/step-2', [ListingWizardController::class, 'storeStepTwo'])->name('step-two');
        Route::get('/review', [ListingWizardController::class, 'review'])->name('review');
        Route::post('/submit', [ListingWizardController::class, 'submit'])->name('submit');
        Route::get('/photo-url/{propertyPhoto}', [ListingWizardController::class, 'signedPhotoUrl'])->name('photo-url');
    });

    Route::name('buyer.')->group(function () {
        Route::get('/properti-tersimpan', [BuyerController::class, 'savedProperties'])->name('saved-properties');
        Route::get('/jadwal-survei', [BuyerController::class, 'surveySchedule'])->name('survey-schedule');
        Route::get('/transaksi-escrow', [BuyerController::class, 'transactionEscrow'])->name('transaction-escrow');
        Route::get('/profil-kyc', [BuyerController::class, 'profileKYC'])->name('profile-kyc');
    });
});
