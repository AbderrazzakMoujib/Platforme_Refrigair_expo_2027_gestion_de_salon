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
use App\Http\Controllers\Admin\SalonController;
use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\ExhibitorStandOptionsController;
use App\Http\Controllers\CommercialProfileController;
use App\Http\Controllers\CommercialExhibitorController;
use App\Http\Controllers\CommercialEquipmentRequestController;
use App\Http\Controllers\CommercialEquipmentController;
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


    Route::get('/salons', [SalonController::class, 'index'])
    ->name('salons.index');

    Route::get('/salons/create', [SalonController::class, 'create'])
    ->name('salons.create');

    Route::post('/salons', [SalonController::class, 'store'])
    ->name('salons.store');

    Route::get('/salons/{salon}', [SalonController::class, 'show'])
    ->whereNumber('salon')
    ->name('salons.show');

    Route::get('/salons/{salon}/edit', [SalonController::class, 'edit'])
    ->whereNumber('salon')
    ->name('salons.edit');

    Route::put('/salons/{salon}', [SalonController::class, 'update'])
    ->whereNumber('salon')
    ->name('salons.update');

    Route::patch('/salons/{salon}/registration', [
    SalonController::class,
    'toggleRegistration',
     ])
    ->whereNumber('salon')
    ->name('salons.registration');


    Route::get('/badges/print-selected', [
    BadgeController::class, 'printSelected',
     ])->name('badges.print-selected');

    Route::get('/badges/export', [
    BadgeController::class, 'export',
     ])->name('badges.export');

    Route::get('/badges/verify/{token}', [
    BadgeController::class, 'verify',
     ])->whereUuid('token')->name('badges.verify');

    Route::get('/badges', [
    BadgeController::class, 'index',
     ])->name('badges.index');

    Route::get('/badges/create', [
    BadgeController::class, 'create',
     ])->name('badges.create');

    Route::post('/badges', [
    BadgeController::class, 'store',
     ])->name('badges.store');

    Route::patch('/badges/exhibitors/{exhibitor}/quota', [
    BadgeController::class, 'updateQuota',
     ])->whereNumber('exhibitor')->name('badges.quota');

    Route::get('/badges/{badge}', [
    BadgeController::class, 'show',
     ])->whereNumber('badge')->name('badges.show');

    Route::get('/badges/{badge}/edit', [
    BadgeController::class, 'edit',
     ])->whereNumber('badge')->name('badges.edit');

    Route::put('/badges/{badge}', [
    BadgeController::class, 'update',
     ])->whereNumber('badge')->name('badges.update');

    Route::patch('/badges/{badge}/toggle', [
    BadgeController::class, 'toggle',
     ])->whereNumber('badge')->name('badges.toggle');

    Route::delete('/badges/{badge}', [
    BadgeController::class, 'destroy',
     ])->whereNumber('badge')->name('badges.destroy');

    Route::get('/badges/{badge}/print', [
    BadgeController::class, 'print',
     ])->whereNumber('badge')->name('badges.print');

    Route::get(
    '/assistance',
    [\App\Http\Controllers\Admin\SupportController::class, 'index']
    )->name('support.index');

    Route::get(
    '/assistance/{ticket}',
    [\App\Http\Controllers\Admin\SupportController::class, 'show']
    )->whereNumber('ticket')->name('support.show');

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

    // Badges des exposants du commercial
    Route::get('/commercial/badges', [App\Http\Controllers\CommercialBadgeController::class, 'index'])->name('commercial.badges.index');
    Route::get('/commercial/badges/export', [App\Http\Controllers\CommercialBadgeController::class, 'export'])->name('commercial.badges.export');
    Route::post('/commercial/badges/{badge}/print', [App\Http\Controllers\CommercialBadgeController::class, 'print'])->whereNumber('badge')->name('commercial.badges.print');
    Route::post('/commercial/badges/exposants/{exhibitor}/print', [App\Http\Controllers\CommercialBadgeController::class, 'printExhibitor'])->whereNumber('exhibitor')->name('commercial.badges.print-exhibitor');
    Route::patch('/commercial/badges/exposants/{exhibitor}/quota', [App\Http\Controllers\CommercialBadgeController::class, 'updateQuota'])->whereNumber('exhibitor')->name('commercial.badges.quota');
    
    Route::get(
    '/commercial/assistance',
    [\App\Http\Controllers\CommercialSupportController::class, 'index']
    )->name('commercial.support.index');

    Route::get(
    '/commercial/assistance/{ticket}',
    [\App\Http\Controllers\CommercialSupportController::class, 'show']
    )->whereNumber('ticket')->name('commercial.support.show');

    Route::post(
    '/commercial/assistance/{ticket}/reply',
    [\App\Http\Controllers\CommercialSupportController::class, 'reply']
    )->middleware('throttle:20,1')
    ->whereNumber('ticket')->name('commercial.support.reply');

    Route::patch(
    '/commercial/assistance/{ticket}/status',
    [\App\Http\Controllers\CommercialSupportController::class, 'updateStatus']
    )->whereNumber('ticket')->name('commercial.support.status');


    Route::get('/commercial/notifications',
    [\App\Http\Controllers\AccountNotificationController::class, 'index']
    )->name('commercial.notifications.index');

    Route::post('/commercial/notifications/read-all',
    [\App\Http\Controllers\AccountNotificationController::class, 'markAllRead']
    )->name('commercial.notifications.read-all');

    Route::post('/commercial/notifications/{notification}/open',
    [\App\Http\Controllers\AccountNotificationController::class, 'open']
   )->whereUuid('notification')
  ->name('commercial.notifications.open');

  Route::get(
    '/commercial/demandes-equipements',
    [CommercialEquipmentRequestController::class, 'index']
  )->name('commercial.equipment-requests.index');

  Route::get(
    '/commercial/demandes-equipements/{equipmentRequest}',
    [CommercialEquipmentRequestController::class, 'show']
  )->whereNumber('equipmentRequest')
    ->name('commercial.equipment-requests.show');

    Route::get(
    '/commercial/demandes-equipements/create',
    [CommercialEquipmentRequestController::class, 'create']
    )->name('commercial.equipment-requests.create');

    Route::post(
    '/commercial/demandes-equipements',
    [CommercialEquipmentRequestController::class, 'store']
    )->name('commercial.equipment-requests.store');

    // Gestion des équipements du commercial

Route::get('/commercial/equipements',
    [CommercialEquipmentController::class, 'index']
)->name('commercial.equipements.index');

Route::get('/commercial/equipements/create',
    [CommercialEquipmentController::class, 'create']
)->name('commercial.equipements.create');

Route::post('/commercial/equipements',
    [CommercialEquipmentController::class, 'store']
)->name('commercial.equipements.store');

Route::get('/commercial/equipements/{equipment}/edit',
    [CommercialEquipmentController::class, 'edit']
)->whereNumber('equipment')
 ->name('commercial.equipements.edit');

Route::put('/commercial/equipements/{equipment}',
    [CommercialEquipmentController::class, 'update']
)->whereNumber('equipment')
 ->name('commercial.equipements.update');

Route::patch('/commercial/equipements/{equipment}/toggle',
    [CommercialEquipmentController::class, 'toggle']
)->whereNumber('equipment')
 ->name('commercial.equipements.toggle');

});

Route::middleware(['auth', 'role:exposant'])->group(function (): void {
    Route::get('/exposant', [App\Http\Controllers\ExhibitorDashboardController::class, '__invoke'])->name('exposant.index');
    Route::resource('/exposant/badges', ExhibitorBadgeController::class)->except(['show'])->names('exhibitor.badges');
    Route::get(
    '/exposant/options-du-stand',
    [ExhibitorStandOptionsController::class, 'index']
    )->name('exhibitor.stand-options.index');

    Route::post(
    '/exposant/options-du-stand',
    [ExhibitorStandOptionsController::class, 'store']
    )->name('exhibitor.stand-options.store');


    Route::get(
    '/exposant/mon-entreprise',
    [
        \App\Http\Controllers\ExhibitorCompanyController::class,
        'index'
    ]
    )->name('exhibitor.company.index');

    Route::put(
    '/exposant/mon-entreprise',
    [
        \App\Http\Controllers\ExhibitorCompanyController::class,
        'update'
    ]
    )->name('exhibitor.company.update');

    
    Route::get(
    '/exposant/badges/{badge}/qr',
    [ExhibitorBadgeController::class, 'qr']
    )
    ->whereNumber('badge')
    ->name('exhibitor.badges.qr');


    Route::get(
    '/exposant/mes-demandes',
    [
        \App\Http\Controllers\ExhibitorRequestController::class,
        'index',
    ]
    )->name('exhibitor.requests.index');

    
    Route::get(
    '/exposant/assistance',
    [\App\Http\Controllers\ExhibitorSupportController::class, 'index']
     )->name('exhibitor.support.index');

    Route::post(
    '/exposant/assistance',
    [\App\Http\Controllers\ExhibitorSupportController::class, 'store']
     )->middleware('throttle:10,1')
    ->name('exhibitor.support.store');

    Route::get(
    '/exposant/assistance/{ticket}',
    [\App\Http\Controllers\ExhibitorSupportController::class, 'show']
    )->whereNumber('ticket')->name('exhibitor.support.show');

    Route::post(
    '/exposant/assistance/{ticket}/reply',
    [\App\Http\Controllers\ExhibitorSupportController::class, 'reply']
    )->middleware('throttle:20,1')
    ->whereNumber('ticket')->name('exhibitor.support.reply');

    Route::get('/exposant/notifications',
    [\App\Http\Controllers\AccountNotificationController::class, 'index']
    )->name('exhibitor.notifications.index');

    Route::post('/exposant/notifications/read-all',
    [\App\Http\Controllers\AccountNotificationController::class, 'markAllRead']
    )->name('exhibitor.notifications.read-all');

    Route::post('/exposant/notifications/{notification}/open',
    [\App\Http\Controllers\AccountNotificationController::class, 'open']
    )->whereUuid('notification')
    ->name('exhibitor.notifications.open');


});


Route::get(
    '/notifications/live',
    [
        \App\Http\Controllers\LiveNotificationController::class,
        'index',
    ]
)
    ->middleware([
        'auth',
        'active.account',
        'throttle:60,1',
    ])
    ->name('notifications.live');

