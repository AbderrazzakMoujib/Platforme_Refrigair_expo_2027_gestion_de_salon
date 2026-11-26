<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\CommercialInvitationController;
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
use App\Http\Controllers\Admin\ConferenceManagerController;
use App\Http\Controllers\ExhibitorStandOptionsController;
use App\Http\Controllers\CommercialProfileController;
use App\Http\Controllers\CommercialExhibitorController;
use App\Http\Controllers\CommercialEquipmentRequestController;
use App\Http\Controllers\CommercialEquipmentController;
use App\Http\Controllers\ResponsableConferenceNotificationController;
use App\Http\Controllers\ConferenceCollaborationController;
use App\Http\Controllers\ConferenceSupportInboxController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExhibitorBadgeController;
use App\Http\Controllers\ResponsableConferenceController;


Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['active.account', 'guest'])->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware(['throttle:10,1'])->group(function (): void {
    Route::get('/account/activation/{token}', [CommercialInvitationController::class, 'show'])
        ->whereAlphaNumeric('token')->name('account-activation.show');
    Route::post('/account/activation/{token}', [CommercialInvitationController::class, 'accept'])
        ->whereAlphaNumeric('token')->name('account-activation.accept');
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
    Route::post('/commerciaux/{commercial}/invitation', [CommercialController::class, 'resendInvitation'])->whereNumber('commercial')->name('commerciaux.invitation');
    Route::get('/responsables-conferences', [ConferenceManagerController::class, 'index'])->name('responsables-conferences.index');
    Route::get('/responsables-conferences/create', [ConferenceManagerController::class, 'create'])->name('responsables-conferences.create');
    Route::post('/responsables-conferences', [ConferenceManagerController::class, 'store'])->name('responsables-conferences.store');
    Route::get('/responsables-conferences/{manager}/edit', [ConferenceManagerController::class, 'edit'])->whereNumber('manager')->name('responsables-conferences.edit');
    Route::put('/responsables-conferences/{manager}', [ConferenceManagerController::class, 'update'])->whereNumber('manager')->name('responsables-conferences.update');
    Route::put('/responsables-conferences/{manager}/password', [ConferenceManagerController::class, 'resetPassword'])->whereNumber('manager')->name('responsables-conferences.password');
    Route::patch('/responsables-conferences/{manager}/access', [ConferenceManagerController::class, 'toggleAccess'])->whereNumber('manager')->name('responsables-conferences.access');
    Route::post('/responsables-conferences/{manager}/invitation', [ConferenceManagerController::class, 'resendInvitation'])->whereNumber('manager')->name('responsables-conferences.invitation');
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

    // Conférences & Lounge des Technologies (consultation)
    Route::get('/conferences', [\App\Http\Controllers\Admin\SlotController::class, 'index'])->name('slots.index');
    Route::get('/conferences/creneaux/{slot}/support', [\App\Http\Controllers\Admin\SlotController::class, 'downloadSupport'])->whereNumber('slot')->name('slots.support');
    Route::get('/conferences/export/programme-{locale}.pdf', [\App\Http\Controllers\SlotExportController::class, 'pdf'])->whereIn('locale', ['fr', 'en'])->name('slots.export.pdf');
    Route::get('/conferences/export/creneaux.csv', [\App\Http\Controllers\SlotExportController::class, 'csv'])->name('slots.export.csv');

});

// Vérification réservée à l'Admin et au Commercial propriétaire de l'exposant.
// Le token seul ne permet jamais d'accéder aux données personnelles.
Route::get('/admin/badges/verify/{token}', [BadgeController::class, 'verify'])
    ->middleware(['auth', 'role:admin,commercial'])
    ->whereUuid('token')
    ->name('admin.badges.verify');


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
    Route::post('/commercial/exposants/{exhibitor}/invitation', [CommercialExhibitorController::class, 'resendInvitation'])->whereNumber('exhibitor')->name('commercial.exhibitors.invitation');
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


 Route::patch(
    '/commercial/demandes-equipements/{equipmentRequest}/approve',
    [\App\Http\Controllers\CommercialEquipmentRequestController::class, 'approve']
)->whereNumber('equipmentRequest')
  ->name('commercial.equipment-requests.approve');

Route::patch(
    '/commercial/demandes-equipements/{equipmentRequest}/reject',
    [\App\Http\Controllers\CommercialEquipmentRequestController::class, 'reject']
)->whereNumber('equipmentRequest')
  ->name('commercial.equipment-requests.reject');

    // Conférences & Lounge des Technologies
    Route::prefix('/commercial/conferences')->name('commercial.')->group(function (): void {
        Route::get('/', [\App\Http\Controllers\CommercialSlotController::class, 'index'])->name('slots.index');
        Route::put('/creneaux/{slot}', [\App\Http\Controllers\CommercialSlotController::class, 'update'])->whereNumber('slot')->name('slots.update');
        Route::get('/creneaux/{slot}/support', [\App\Http\Controllers\CommercialSlotController::class, 'downloadSupport'])->whereNumber('slot')->name('slots.support');
        Route::post('/creneaux/{slot}/support', [\App\Http\Controllers\CommercialSlotController::class, 'uploadSupport'])->middleware('throttle:20,1')->whereNumber('slot')->name('slots.support.store');

        Route::get('/demandes', [\App\Http\Controllers\CommercialSlotRequestController::class, 'index'])->name('slot-requests.index');
        Route::post('/demandes', [\App\Http\Controllers\CommercialSlotRequestController::class, 'store'])->middleware('throttle:10,1')->name('slot-requests.store');

        Route::get('/export/programme-{locale}.pdf', [\App\Http\Controllers\SlotExportController::class, 'pdf'])->whereIn('locale', ['fr', 'en'])->name('slots.export.pdf');
        Route::get('/export/creneaux.csv', [\App\Http\Controllers\SlotExportController::class, 'csv'])->name('slots.export.csv');
    });

});

Route::middleware(['auth', 'role:responsable_conferences'])->prefix('responsable-conferences')->name('responsable-conferences.')->group(function (): void {
    Route::get('/', [ResponsableConferenceController::class, 'dashboard'])->name('dashboard');
    Route::get('/planning', [ResponsableConferenceController::class, 'planning'])->name('planning');
    Route::get('/demandes', [ResponsableConferenceController::class, 'requests'])->name('requests.index');
    Route::get('/reservations-confirmees', [ResponsableConferenceController::class, 'confirmed'])->name('confirmed');
    Route::patch('/demandes/{slotRequest}/accepter', [ResponsableConferenceController::class, 'accept'])->whereNumber('slotRequest')->name('requests.accept');
    Route::patch('/demandes/{slotRequest}/refuser', [ResponsableConferenceController::class, 'reject'])->whereNumber('slotRequest')->name('requests.reject');
    Route::get('/historique', [ResponsableConferenceController::class, 'history'])->name('history');
    Route::get('/profil', [ResponsableConferenceController::class, 'profile'])->name('profile');
    Route::put('/profil', [ResponsableConferenceController::class, 'updateProfile'])->name('profile.update');
    Route::get(
    '/reservations-confirmees/export',
    [ResponsableConferenceController::class, 'exportConfirmed']
)->name('confirmed.export');

Route::get(
    '/historique/export',
    [ResponsableConferenceController::class, 'exportHistory']
)->name('history.export');

Route::put(
    '/profil/mot-de-passe',
    [ResponsableConferenceController::class, 'updatePassword']
)->middleware('throttle:5,1')
  ->name('profile.password');


Route::get(
    '/notifications',
    [ResponsableConferenceNotificationController::class, 'index']
)->name('notifications.index');

Route::post(
    '/notifications/read-all',
    [ResponsableConferenceNotificationController::class, 'markAllRead']
)->name('notifications.read-all');

Route::post(
    '/notifications/{notification}/open',
    [ResponsableConferenceNotificationController::class, 'open']
)->whereUuid('notification')
 ->name('notifications.open');


Route::get(
    '/supports',
    [ConferenceSupportInboxController::class, 'index']
)->name('supports.index');

Route::get(
    '/supports/{slotRequest}',
    [ConferenceCollaborationController::class, 'show']
)->whereNumber('slotRequest')
 ->name('supports.show');

Route::post(
    '/supports/{slotRequest}/review',
    [ConferenceCollaborationController::class, 'reviewSupport']
)->whereNumber('slotRequest')
 ->name('supports.review');

Route::post(
    '/supports/{slotRequest}/messages',
    [ConferenceCollaborationController::class, 'sendMessage']
)->middleware('throttle:20,1')
 ->whereNumber('slotRequest')
 ->name('supports.message');

Route::get(
    '/supports/{slotRequest}/download',
    [ConferenceCollaborationController::class, 'downloadSupport']
)->whereNumber('slotRequest')
 ->name('supports.download');


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

    // Conférences & Lounge des Technologies
    Route::get('/exposant/conferences', [\App\Http\Controllers\ExhibitorSlotController::class, 'index'])->name('exhibitor.slots.index');
    // Ouverture directe / rechargement de l'URL du formulaire : retour à la page.
    Route::get('/exposant/conferences/demandes', fn () => redirect()->route('exhibitor.slots.index'));
    Route::post('/exposant/conferences/demandes',[\App\Http\Controllers\ExhibitorSlotController::class, 'store'])->middleware('throttle:10,1')->name('exhibitor.slots.request');
    Route::post('/exposant/conferences/creneaux/{slot}/support', [\App\Http\Controllers\ExhibitorSlotController::class, 'uploadSupport'])->middleware('throttle:10,1')->whereNumber('slot')->name('exhibitor.slots.support.store');
    Route::get('/exposant/conferences/creneaux/{slot}/support', [\App\Http\Controllers\ExhibitorSlotController::class, 'downloadSupport'])->whereNumber('slot')->name('exhibitor.slots.support.download');

Route::get(
    '/exposant/conferences/demandes/{slotRequest}/suivi',
    [ConferenceCollaborationController::class, 'show']
)->whereNumber('slotRequest')
 ->name('exhibitor.conferences.collaboration.show');

Route::post(
    '/exposant/conferences/demandes/{slotRequest}/support',
    [ConferenceCollaborationController::class, 'uploadSupport']
)->middleware('throttle:10,1')
 ->whereNumber('slotRequest')
 ->name('exhibitor.conferences.collaboration.support');

Route::post(
    '/exposant/conferences/demandes/{slotRequest}/messages',
    [ConferenceCollaborationController::class, 'sendMessage']
)->middleware('throttle:20,1')
 ->whereNumber('slotRequest')
 ->name('exhibitor.conferences.collaboration.message');

Route::get(
    '/exposant/conferences/demandes/{slotRequest}/support/download',
    [ConferenceCollaborationController::class, 'downloadSupport']
)->whereNumber('slotRequest')
 ->name('exhibitor.conferences.collaboration.download');


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

