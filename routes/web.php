<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Dashboard\AdminController;
use App\Http\Controllers\Dashboard\AppointmentController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\LandDeedController;
use App\Http\Controllers\Dashboard\ListingController;
use App\Http\Controllers\Dashboard\NotificationController;
use App\Http\Controllers\Dashboard\PropertyReviewController;
use App\Http\Controllers\Dashboard\RolePermissionController;
use App\Http\Controllers\Dashboard\SavedPropertyController;
use App\Http\Controllers\Dashboard\SuperadminController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingAiController;
use App\Http\Controllers\ListingWizardController;
use App\Http\Controllers\PanelAuthController;
use App\Http\Controllers\ProfilePasswordController;
use App\Http\Controllers\RegionDataController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'home'])->name('home');
Route::get('/kalkulator-pajak', [HomeController::class, 'taxCalculator'])->name('tax-calculator');
Route::get('/detail-properti/{uuid}', [HomeController::class, 'propertyDetails'])
    ->whereUuid('uuid')
    ->name('property-details');
Route::post('/detail-properti/{uuid}/jadwalkan', [HomeController::class, 'scheduleVisit'])
    ->whereUuid('uuid')
    ->name('property.schedule-visit');
Route::get('/properti', [HomeController::class, 'property'])->name('properties');
Route::get('/pengguna/{uuid}', [HomeController::class, 'userDetails'])
    ->whereUuid('uuid')
    ->name('user-details');
Route::get('/tentang', [HomeController::class, 'about'])->name('about');
Route::get('/kontak', [HomeController::class, 'contact'])->name('contact');
Route::post('/api/chat', [ChatbotController::class, 'ask'])
    ->middleware('throttle:10,1')
    ->name('chatbot.reply');

Route::get('/masuk', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/masuk', [AuthController::class, 'login']);
Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');
Route::post('/daftar', [AuthController::class, 'register'])->name('register');

Route::prefix('panel')->name('panel.')->group(function () {
    Route::get('/', [PanelAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/', [PanelAuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.store');
    Route::post('/keluar', [PanelAuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [DashboardController::class, 'profile'])->name('profile');
    Route::put('/profil/password', [ProfilePasswordController::class, 'update'])
        ->middleware('throttle:5,1')
        ->name('profile.password.update');
    Route::post('/profil/avatar', [DashboardController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profil/avatar', [DashboardController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('detail-notif');
    Route::get('/notifikasi/belum-dibaca', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::patch('/notifikasi/{notification}/dibaca', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::name('seller.')->group(function () {
        Route::get('/listing', [ListingController::class, 'index'])->name('listing');
        Route::get('/properti-saya', [ListingController::class, 'myProperties'])->name('my-properties');
        Route::get('/permintaan-survei', [ListingController::class, 'requestSurvey'])->name('request-survey');
        Route::get('/transaksi-penjualan', [ListingController::class, 'sellingTransaction'])
            ->middleware('permission:transactions.view_own_tracker')
            ->name('selling-transaction');
        Route::get('/rekening-pencairan', [ListingController::class, 'disbursementAccount'])->name('disbursement-account');
    });
    Route::prefix('listing')->name('listing.')->group(function () {
        Route::get('/buat', [ListingWizardController::class, 'create'])->name('create');
        Route::get('/wilayah/provinces', [RegionDataController::class, 'provinces'])->name('regions.provinces');
        Route::get('/wilayah/provinces/{provinceCode}/regencies', [RegionDataController::class, 'regencies'])
            ->where('provinceCode', '\\d{2}')
            ->name('regions.regencies');
        Route::get('/wilayah/regencies/{regencyCode}/districts', [RegionDataController::class, 'districts'])
            ->where('regencyCode', '\\d{2}\\.\\d{2}')
            ->name('regions.districts');
        Route::post('/langkah-1', [ListingWizardController::class, 'storeStepOne'])
            ->middleware('permission:properties.create')
            ->name('step-one');
        Route::post('/langkah-2', [ListingWizardController::class, 'storeStepTwo'])
            ->middleware(['permission:properties.create', 'permission:documents.upload_basic'])
            ->name('step-two');
        Route::post('/ai/ekstrak', [ListingAiController::class, 'extract'])
            ->middleware(['throttle:10,1', 'permission:properties.create'])
            ->name('ai.extract');
        Route::post('/ai/deskripsi', [ListingAiController::class, 'description'])
            ->middleware(['throttle:10,1', 'permission:properties.create'])
            ->name('ai.description');
        Route::get('/tinjau-ulang', [ListingWizardController::class, 'review'])->name('review');
        Route::post('/buat', [ListingWizardController::class, 'submit'])
            ->middleware('permission:properties.create')
            ->name('submit');
        Route::get('/photo-url/{propertyPhoto}', [ListingWizardController::class, 'signedPhotoUrl'])->name('photo-url');
    });

    Route::name('buyer.')->group(function () {
        Route::get('/properti-tersimpan', [UserController::class, 'savedProperties'])->name('saved-properties');
        Route::post('/properti/{uuid}/simpan', [UserController::class, 'saveProperty'])
            ->whereUuid('uuid')
            ->name('saved-properties.store');
        Route::delete('/properti-tersimpan/{uuid}', [UserController::class, 'unsaveProperty'])
            ->whereUuid('uuid')
            ->name('saved-properties.destroy');
        Route::post('/properti/simpan/toggle', [SavedPropertyController::class, 'toggle'])
            ->middleware('throttle:60,1')
            ->name('saved-properties.toggle');
        Route::get('/jadwal-survei', [UserController::class, 'surveySchedule'])->name('survey-schedule');
        Route::post('/jadwal-survei/{appointment}/jadwal-pengganti', [AppointmentController::class, 'respondToReschedule'])
            ->name('survey-schedule.reschedule');
        Route::get('/transaksi-escrow', [UserController::class, 'transactionEscrow'])
            ->middleware('permission:transactions.view_own_tracker')
            ->name('transaction-escrow');
    });

    Route::post('/jadwal-survei/{appointment}/status', [AppointmentController::class, 'updateStatus'])
        ->name('appointments.status');

    Route::name('land-deed.')->group(function () {
        Route::get('/dashboard-antrian', [LandDeedController::class, 'index'])
            ->middleware('permission:appointments.view_all')
            ->name('index');
        Route::post('/dashboard-antrian/{appointment}/status', [AppointmentController::class, 'updateStatus'])
            ->name('appointments.status');
        Route::get('/pengecekan-bpn', [LandDeedController::class, 'bpnCheck'])
            ->middleware('permission:documents.input_bpn_result|transactions.mark_bpn_cleared')
            ->name('bpn-check');
        Route::get('/penandatanganan-ajb', [LandDeedController::class, 'ajbSigning'])
            ->middleware('permission:transactions.schedule_ajb')
            ->name('ajb-signing');
        Route::get('/manajemen-akta', [LandDeedController::class, 'deedManagement'])
            ->middleware('permission:documents.verify_status')
            ->name('deed-management');
        Route::get('/validasi-pajak', [LandDeedController::class, 'taxValidation'])
            ->middleware('permission:documents.verify_status')
            ->name('tax-validation');
        Route::get('/resi-balik-nama', [LandDeedController::class, 'transferForOwnership'])
            ->middleware('permission:documents.verify_status')
            ->name('transfer-for-ownership');
    });

    Route::prefix('superadmin')->name('superadmin.')->group(function () {
        Route::middleware('role:Superadmin')->group(function () {
            Route::get('/manajemen-user', [SuperadminController::class, 'userManagement'])->name('user-management');
            Route::get('/manajemen-user/tambah', [SuperadminController::class, 'createUser'])->name('user-management.create');
            Route::post('/manajemen-user', [SuperadminController::class, 'storeUser'])->name('user-management.store');
            Route::get('/konfigurasi-sistem', [RolePermissionController::class, 'index'])->name('configuration-system');
            Route::put('/konfigurasi-sistem/roles/{role}/permissions', [RolePermissionController::class, 'update'])
                ->whereNumber('role')
                ->name('configuration-system.permissions.update');
        });
        Route::get('/transaksi-escrow', [SuperadminController::class, 'transactionAndEscrow'])
            ->middleware(['role:Superadmin', 'permission:transactions.view_all'])
            ->name('transaction-and-escrow');
        Route::get('/master-data-properti', [SuperadminController::class, 'masterDataProperty'])
            ->middleware('role:Superadmin')
            ->name('master-data-property');
        Route::get('/audit-log', [SuperadminController::class, 'auditLog'])
            ->middleware(['role:Superadmin', 'permission:system.view_audit_logs'])
            ->name('audit-log');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/manajemen-user', [AdminController::class, 'userManagement'])
            ->middleware('permission:users.view_all_users')
            ->name('user-management');
        Route::get('/transaksi-escrow', [AdminController::class, 'transactionAndEscrow'])
            ->middleware('permission:transactions.view_all')
            ->name('transaction-and-escrow');
        Route::get('/master-data-properti', [AdminController::class, 'masterDataProperty'])
            ->middleware([
                'permission:properties.approve_listing|properties.reject_listing',
                'permission:documents.view_sensitive_pii',
            ])
            ->name('master-data-property');
        Route::get('/audit-log', [AdminController::class, 'auditLog'])
            ->middleware('permission:system.view_audit_logs')
            ->name('audit-log');
    });

    Route::prefix('property-review')->name('property-review.')->group(function () {
        Route::get('/properties/{property}/ppats', [PropertyReviewController::class, 'ppats'])
            ->middleware('permission:properties.approve_listing|properties.reject_listing')
            ->name('ppats');
        Route::post('/properties/{property}/ppat', [PropertyReviewController::class, 'assignPpat'])
            ->middleware('permission:properties.approve_listing|properties.reject_listing')
            ->name('assign-ppat');
        Route::post('/properties/{property}/status', [PropertyReviewController::class, 'updateStatus'])
            ->middleware('permission:properties.approve_listing|properties.reject_listing')
            ->name('status');
        Route::get('/files/{propertyPhoto}', [PropertyReviewController::class, 'showFile'])
            ->middleware('permission:documents.view_sensitive_pii')
            ->name('file');
    });
});
