<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Admin\CommercialController;
use App\Http\Controllers\Admin\ExhibitorController;
use App\Http\Controllers\Admin\EquipmentController;
use App\Http\Controllers\Admin\EquipmentRequestController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\CommercialProfileController;
use App\Http\Controllers\CommercialExhibitorController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExhibitorBadgeController;


Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['active.account', 'guest'])->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:admin'])->get('/admin', AdminDashboardController::class)->name('admin.index');
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/commerciaux', [CommercialController::class, 'index'])->name('commerciaux.index');
    Route::get('/commerciaux/create', [CommercialController::class, 'create'])->name('commerciaux.create');
    Route::post('/commerciaux', [CommercialController::class, 'store'])->name('commerciaux.store');
    Route::get('/commerciaux/{commercial}', [CommercialController::class, 'show'])->whereNumber('commercial')->name('commerciaux.show');
    Route::get('/commerciaux/{commercial}/edit', [CommercialController::class, 'edit'])->whereNumber('commercial')->name('commerciaux.edit');
    Route::put('/commerciaux/{commercial}', [CommercialController::class, 'update'])->whereNumber('commercial')->name('commerciaux.update');
    Route::get('/exposants', [ExhibitorController::class, 'index'])
    ->name('exposants.index');

    Route::get('/exposants/{exhibitor}', [ExhibitorController::class, 'show'])
    ->whereNumber('exhibitor')
    ->name('exposants.show');
    Route::get('/equipements', [EquipmentController::class, 'index'])
    ->name('equipements.index');

    Route::get('/equipements/create', [EquipmentController::class, 'create'])
    ->name('equipements.create');

     Route::post('/equipements', [EquipmentController::class, 'store'])
    ->name('equipements.store');

    Route::get('/equipements/{equipment}/edit', [EquipmentController::class, 'edit'])
    ->whereNumber('equipment')
    ->name('equipements.edit');

    Route::put('/equipements/{equipment}', [EquipmentController::class, 'update'])
    ->whereNumber('equipment')
    ->name('equipements.update');

    Route::patch('/equipements/{equipment}/toggle', [EquipmentController::class, 'toggle'])
    ->whereNumber('equipment')
    ->name('equipements.toggle');

    Route::get('/demandes', [EquipmentRequestController::class, 'index'])
    ->name('demandes.index');

    Route::get('/demandes/{equipmentRequest}', [EquipmentRequestController::class, 'show'])
    ->whereNumber('equipmentRequest')
    ->name('demandes.show');

    Route::patch('/demandes/{equipmentRequest}/approve', [EquipmentRequestController::class, 'approve'])
    ->whereNumber('equipmentRequest')
    ->name('demandes.approve');

    Route::patch('/demandes/{equipmentRequest}/reject', [EquipmentRequestController::class, 'reject'])
    ->whereNumber('equipmentRequest')
    ->name('demandes.reject');

    Route::get('/statistiques', StatisticsController::class)
    ->name('statistiques.index');

    Route::get('/notifications', [NotificationController::class, 'index'])
    ->name('notifications.index');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
    ->name('notifications.read-all');

    Route::post('/notifications/{notification}/open', [NotificationController::class, 'open'])
    ->name('notifications.open');

    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])
    ->name('notifications.read');

    Route::get('/parametres', [SettingsController::class, 'edit'])
    ->name('parametres.index');

    Route::put('/parametres/profil', [SettingsController::class, 'updateProfile'])
    ->name('parametres.profil');

    Route::put('/parametres/password', [SettingsController::class, 'updatePassword'])
    ->middleware('throttle:5,1')
    ->name('parametres.password');

});


Route::middleware(['auth', 'role:commercial'])->group(function (): void {
    Route::get('/commercial', [App\Http\Controllers\CommercialDashboardController::class, '__invoke'])->name('commercial.index');
    Route::get('/commercial/profil', [CommercialProfileController::class, 'edit'])->name('commercial.profile.edit');
    Route::put('/commercial/profil', [CommercialProfileController::class, 'update'])->name('commercial.profile.update');
    Route::get('/commercial/exposants', [CommercialExhibitorController::class, 'index'])->name('commercial.exhibitors.index');
    Route::get('/commercial/exposants/create', [CommercialExhibitorController::class, 'create'])->name('commercial.exhibitors.create');
    Route::post('/commercial/exposants', [CommercialExhibitorController::class, 'store'])->name('commercial.exhibitors.store');
    Route::get('/commercial/exposants/{exhibitor}', [CommercialExhibitorController::class, 'show'])->whereNumber('exhibitor')->name('commercial.exhibitors.show');
    Route::get('/commercial/exposants/{exhibitor}/edit', [CommercialExhibitorController::class, 'edit'])->whereNumber('exhibitor')->name('commercial.exhibitors.edit');
    Route::put('/commercial/exposants/{exhibitor}', [CommercialExhibitorController::class, 'update'])->whereNumber('exhibitor')->name('commercial.exhibitors.update');
    Route::post('/commercial/exposants/{exhibitor}/access', [CommercialExhibitorController::class, 'createAccess'])->whereNumber('exhibitor')->name('commercial.exhibitors.access');
    Route::patch('/commercial/exposants/{exhibitor}/access', [CommercialExhibitorController::class, 'toggleAccess'])->whereNumber('exhibitor')->name('commercial.exhibitors.access.toggle');
});
Route::middleware(['auth', 'role:exposant'])->group(function (): void {
    Route::get('/exposant', [App\Http\Controllers\ExhibitorDashboardController::class, '__invoke'])->name('exposant.index');
    Route::resource('/exposant/badges', ExhibitorBadgeController::class)->except(['show'])->names('exhibitor.badges');
});
