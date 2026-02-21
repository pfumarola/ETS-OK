<?php

use App\Http\Controllers\AccountingReportController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\RendicontoCassaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ElezioneController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\IncaricoController;
use App\Http\Controllers\OrganoController;
use App\Http\Controllers\CaricaSocialeController;
use App\Http\Controllers\ExpenseRefundController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\IncassoController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberInviteController;
use App\Http\Controllers\MemberTypeController;
use App\Http\Controllers\ContoController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PublicDownloadController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PrimaNotaController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerbaleController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['install'])->prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallController::class, 'show'])->name('show');
    Route::get('/back', [InstallController::class, 'back'])->name('back');
    Route::get('/database', [InstallController::class, 'showDatabaseForm'])->name('database.form');
    Route::post('/database', [InstallController::class, 'storeDatabase'])->name('database');
    Route::get('/skip-smtp', [InstallController::class, 'skipSmtp'])->name('skip-smtp');
    Route::post('/smtp', [InstallController::class, 'storeSmtp'])->name('smtp');
    Route::post('/complete', [InstallController::class, 'complete'])->name('complete');
});

Route::get('/', PublicSiteController::class)->middleware('redirectToInstall')->name('home');

// Domanda di ammissione socio (link invito email) - pubblico, con throttle
Route::middleware('throttle:10,1')->group(function () {
    Route::get('members/admission-request/{token}', [MemberInviteController::class, 'showAdmissionRequestForm'])->name('members.admission-request.form');
});
Route::middleware('throttle:5,1')->group(function () {
    Route::post('members/admission-request/{token}', [MemberInviteController::class, 'storeAdmissionRequest'])->name('members.admission-request.store');
});

Route::middleware('signed')->group(function () {
    Route::get('/public/logo/{attachment}', [PublicDownloadController::class, 'logo'])->name('public.logo.show');
    Route::get('/public/statuto/{attachment}', [PublicDownloadController::class, 'statuto'])->name('public.statuto.download');
    Route::get('/public/rendiconto/{anno}', [PublicDownloadController::class, 'rendiconto'])->name('public.rendiconto.download');
    Route::get('/public/section-background/{sectionId}/{attachment}', [PublicDownloadController::class, 'sectionBackground'])->name('public.section-background.show');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Utenti (solo admin)
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::put('users/{user}/roles', [UserController::class, 'updateRoles'])->name('users.roles.update');
    Route::put('users/{user}/member', [UserController::class, 'linkMember'])->name('users.member.update');
    Route::post('users/{user}/send-password-reset', [UserController::class, 'sendPasswordResetLink'])->name('users.send-password-reset');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Soci e volontari
    Route::get('members/invites/create', [MemberInviteController::class, 'create'])->name('members.invites.create')->middleware('role:admin,segreteria');
    Route::post('members/invites', [MemberInviteController::class, 'store'])->name('members.invites.store')->middleware('role:admin,segreteria');
    Route::resource('members', MemberController::class);
    Route::post('members/{member}/accept-admission', [MemberController::class, 'acceptAdmission'])->name('members.accept-admission');
    Route::post('members/{member}/reject-admission', [MemberController::class, 'rejectAdmission'])->name('members.reject-admission');
    Route::post('members/{member}/communicate-rejection', [MemberController::class, 'communicateRejection'])->name('members.communicate-rejection');
    Route::post('members/{member}/register-appeal', [MemberController::class, 'registerAppeal'])->name('members.register-appeal');
    Route::post('members/{member}/assembly-outcome', [MemberController::class, 'assemblyOutcome'])->name('members.assembly-outcome');
    Route::post('members/{member}/register-death', [MemberController::class, 'registerDeath'])->name('members.register-death');
    Route::post('members/{member}/register-morosita', [MemberController::class, 'registerMorosita'])->name('members.register-morosita');
    Route::post('members/{member}/register-dimissioni', [MemberController::class, 'registerDimissioni'])->name('members.register-dimissioni');
    Route::post('members/{member}/register-esclusione', [MemberController::class, 'registerEsclusione'])->name('members.register-esclusione');
    Route::post('members/{member}/enable-access', [MemberController::class, 'enableMemberAccess'])->name('members.enable-access');
    Route::post('members/{member}/revoke-access', [MemberController::class, 'revokeMemberAccess'])->name('members.revoke-access');
    Route::put('members/{member}/user-roles', [MemberController::class, 'updateMemberUserRoles'])->name('members.user-roles.update')->middleware('role:admin');
    Route::post('members/{member}/incarichi', [IncaricoController::class, 'store'])->name('members.incarichi.store');
    Route::put('incarichi/{incarico}', [IncaricoController::class, 'update'])->name('incarichi.update');
    Route::delete('incarichi/{incarico}', [IncaricoController::class, 'destroy'])->name('incarichi.destroy');
    Route::get('libro-soci', [MemberController::class, 'libroSoci'])->name('libro-soci.index');
    Route::resource('member-types', MemberTypeController::class)->except(['show']);
    Route::resource('organi', OrganoController::class);
    Route::post('cariche-sociali', [CaricaSocialeController::class, 'store'])->name('cariche-sociali.store');
    Route::put('cariche-sociali/{cariche_sociali}', [CaricaSocialeController::class, 'update'])->name('cariche-sociali.update');
    Route::delete('cariche-sociali/{cariche_sociali}', [CaricaSocialeController::class, 'destroy'])->name('cariche-sociali.destroy');
    Route::middleware('role:admin,segreteria')->group(function () {
        Route::get('elezioni', [ElezioneController::class, 'index'])->name('elezioni.index');
        Route::get('elezioni/create', [ElezioneController::class, 'create'])->name('elezioni.create');
        Route::post('elezioni', [ElezioneController::class, 'store'])->name('elezioni.store');
        Route::get('elezioni/{elezione}', [ElezioneController::class, 'show'])->name('elezioni.show');
        Route::get('elezioni/{elezione}/edit', [ElezioneController::class, 'edit'])->name('elezioni.edit');
        Route::put('elezioni/{elezione}', [ElezioneController::class, 'update'])->name('elezioni.update');
        Route::delete('elezioni/{elezione}', [ElezioneController::class, 'destroy'])->name('elezioni.destroy');
        Route::post('elezioni/{elezione}/open', [ElezioneController::class, 'open'])->name('elezioni.open');
        Route::post('elezioni/{elezione}/close', [ElezioneController::class, 'close'])->name('elezioni.close');
        Route::post('elezioni/{elezione}/invalida', [ElezioneController::class, 'invalida'])->name('elezioni.invalida');
        Route::post('elezioni/{elezione}/candidati', [ElezioneController::class, 'addCandidato'])->name('elezioni.candidati.store');
        Route::delete('elezioni/{elezione}/candidati/{candidatura}', [ElezioneController::class, 'removeCandidato'])->name('elezioni.candidati.destroy');
        Route::get('elezioni/{elezione}/risultati', [ElezioneController::class, 'risultati'])->name('elezioni.risultati');
    });
    Route::get('elezioni/{elezione}/vota', [ElezioneController::class, 'vota'])->name('elezioni.vota');
    Route::post('elezioni/{elezione}/vota', [ElezioneController::class, 'storeVoto'])->name('elezioni.vota.store');
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('settings/logo', [SettingsController::class, 'uploadLogo'])->name('settings.logo.upload');
    Route::delete('settings/logo', [SettingsController::class, 'deleteLogo'])->name('settings.logo.delete');
    Route::post('settings/site-section-background/{sectionId}', [SettingsController::class, 'uploadSectionBackground'])->name('settings.site-section-background.upload');
    Route::delete('settings/site-section-background/{sectionId}', [SettingsController::class, 'deleteSectionBackground'])->name('settings.site-section-background.delete');
    Route::post('settings/test-email', [SettingsController::class, 'sendTestEmail'])->name('settings.test-email');
    Route::get('settings/letterhead-preview', [SettingsController::class, 'letterheadPreview'])->name('settings.letterhead-preview');
    Route::get('attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');

    // Incassi (quote e donazioni), ricevute
    Route::get('incassi', [IncassoController::class, 'index'])->name('incassi.index');
    Route::get('incassi/create', [IncassoController::class, 'create'])->name('incassi.create');
    Route::post('incassi', [IncassoController::class, 'store'])->name('incassi.store');
    Route::get('incassi/{incasso}', [IncassoController::class, 'show'])->name('incassi.show');
    Route::get('donations', fn () => redirect()->route('incassi.index', ['type' => 'donazione']))->name('donations.redirect');
    Route::get('donations/create', fn () => redirect()->route('incassi.create', ['type' => 'donazione']));
    Route::get('receipts', [ReceiptController::class, 'index'])->name('receipts.index');
    Route::get('receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');
    Route::get('receipts/{receipt}/download', [ReceiptController::class, 'download'])->name('receipts.download');
    Route::post('receipts/{receipt}/send-email', [ReceiptController::class, 'sendEmail'])->name('receipts.send-email');
    Route::post('receipts/{receipt}/regenerate', [ReceiptController::class, 'regenerate'])->name('receipts.regenerate');

    // Rimborsi spese (richiesta → approvazione con contabilizzazione automatica)
    Route::post('expense-refunds/{expense_refund}/approva', [ExpenseRefundController::class, 'approva'])->name('expense-refunds.approva')->middleware('role:admin,contabile');
    Route::post('expense-refunds/{expense_refund}/attachments', [ExpenseRefundController::class, 'storeAttachment'])->name('expense-refunds.attachments.store');
    Route::delete('expense-refunds/{expense_refund}/attachments/{attachment}', [ExpenseRefundController::class, 'destroyAttachment'])->name('expense-refunds.attachments.destroy');
    Route::get('expense-refunds', [ExpenseRefundController::class, 'index'])->name('expense-refunds.index');
    Route::get('expense-refunds/create', [ExpenseRefundController::class, 'create'])->name('expense-refunds.create');
    Route::post('expense-refunds', [ExpenseRefundController::class, 'store'])->name('expense-refunds.store');
    Route::get('expense-refunds/{expense_refund}', [ExpenseRefundController::class, 'show'])->name('expense-refunds.show');
    Route::put('expense-refunds/{expense_refund}', [ExpenseRefundController::class, 'update'])->name('expense-refunds.update');
    Route::get('expense-refunds/{expense_refund}/print', [ExpenseRefundController::class, 'print'])->name('expense-refunds.print');

    // Contabilità
    Route::resource('conti', ContoController::class)->except(['show']);
    Route::get('prima-nota', [PrimaNotaController::class, 'index'])->name('prima-nota.index');
    Route::get('prima-nota/create', [PrimaNotaController::class, 'create'])->name('prima-nota.create');
    Route::post('prima-nota', [PrimaNotaController::class, 'store'])->name('prima-nota.store');
    Route::get('prima-nota/giroconto', [PrimaNotaController::class, 'createGiroconto'])->name('prima-nota.giroconto.create');
    Route::post('prima-nota/giroconto', [PrimaNotaController::class, 'storeGiroconto'])->name('prima-nota.giroconto.store');
    Route::get('prima-nota/{prima_nota_entry}/edit', [PrimaNotaController::class, 'edit'])->name('prima-nota.edit');
    Route::put('prima-nota/{prima_nota_entry}', [PrimaNotaController::class, 'update'])->name('prima-nota.update');
    Route::get('reports/accounting', [AccountingReportController::class, 'index'])->name('reports.accounting');
    Route::get('reports/accounting/export', [AccountingReportController::class, 'export'])->name('reports.accounting.export');
    Route::get('reports/rendiconto-cassa', [RendicontoCassaController::class, 'index'])->name('reports.rendiconto-cassa');
    Route::get('reports/rendiconto-cassa/export-pdf', [RendicontoCassaController::class, 'exportPdf'])->name('reports.rendiconto-cassa.export-pdf');
    Route::post('reports/rendiconto-cassa/export-pdf', [RendicontoCassaController::class, 'exportPdfFromPayload'])->name('reports.rendiconto-cassa.export-pdf.post');

    // Immobili e magazzino
    Route::get('properties', [PropertyController::class, 'index'])->name('properties.index');
    Route::get('properties/create', [PropertyController::class, 'create'])->name('properties.create');
    Route::post('properties', [PropertyController::class, 'store'])->name('properties.store');
    Route::get('properties/{property}', [PropertyController::class, 'show'])->name('properties.show');
    Route::get('properties/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
    Route::put('properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
    Route::delete('properties/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');
    Route::post('properties/{property}/assets', [PropertyController::class, 'storeAsset'])->name('properties.assets.store');
    Route::delete('properties/{property}/assets/{asset}', [PropertyController::class, 'destroyAsset'])->name('properties.assets.destroy');
    Route::get('items', [ItemController::class, 'index'])->name('items.index');
    Route::get('items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('items', [ItemController::class, 'store'])->name('items.store');
    Route::get('items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
    Route::get('locations/create', [LocationController::class, 'create'])->name('locations.create');
    Route::post('locations', [LocationController::class, 'store'])->name('locations.store');
    Route::get('locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit');
    Route::put('locations/{location}', [LocationController::class, 'update'])->name('locations.update');
    Route::delete('locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');
    Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
    Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
    Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');
    Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
    Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
    Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
    Route::post('warehouses/{warehouse}/stocks', [WarehouseController::class, 'storeStock'])->name('warehouses.stocks.store');
    Route::put('warehouses/{warehouse}/stocks/{stock}', [WarehouseController::class, 'updateStock'])->name('warehouses.stocks.update');
    Route::delete('warehouses/{warehouse}/stocks/{stock}', [WarehouseController::class, 'destroyStock'])->name('warehouses.stocks.destroy');
    Route::get('documents/{document}/pdf', [DocumentController::class, 'downloadPdf'])->name('documents.pdf');
    Route::post('documents/{document}/attachments', [DocumentController::class, 'storeAttachment'])->name('documents.attachments.store');
    Route::delete('documents/{document}/attachments/{attachment}', [DocumentController::class, 'destroyAttachment'])->name('documents.attachments.destroy');
    Route::resource('documents', DocumentController::class);
    Route::resource('templates', TemplateController::class)->except(['show']);
    Route::get('verbali/prossimo-numero', [VerbaleController::class, 'prossimoNumero'])->name('verbali.prossimo-numero');
    Route::get('verbali/{verbale}/pdf', [VerbaleController::class, 'downloadPdf'])->name('verbali.pdf');
    Route::post('verbali/{verbale}/conferma', [VerbaleController::class, 'conferma'])->name('verbali.conferma');
    Route::post('verbali/{verbale}/attachments', [VerbaleController::class, 'storeAttachment'])->name('verbali.attachments.store');
    Route::delete('verbali/{verbale}/attachments/{attachment}', [VerbaleController::class, 'destroyAttachment'])->name('verbali.attachments.destroy');
    Route::resource('verbali', VerbaleController::class)->parameters(['verbali' => 'verbale']);

    // Eventi
    Route::get('events', [EventController::class, 'index'])->name('events.index');
    Route::get('events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('events', [EventController::class, 'store'])->name('events.store');
    Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::get('events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('events/{event}/poster', [EventController::class, 'destroyPoster'])->name('events.poster.destroy');
    Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::post('events/{event}/register', [EventController::class, 'register'])->name('events.register');
    Route::delete('events/{event}/registrations/{registration}', [EventController::class, 'unregister'])->name('events.registrations.destroy');
});
