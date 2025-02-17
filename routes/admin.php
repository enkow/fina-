<?php

use App\Http\Controllers\Admin\ClubController;
use App\Http\Controllers\Admin\ClubPaymentMethodController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\GameController;
use App\Http\Controllers\Admin\HelpItemController;
use App\Http\Controllers\Admin\HelpItemImageController;
use App\Http\Controllers\Admin\HelpSectionController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\StatisticController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettlementController;
use Illuminate\Support\Facades\Route;

Route::redirect('dashboard', 'countries');

Route::resource('customers', CustomerController::class)->only('index', 'edit', 'update', 'destroy');
Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export');
Route::get('/customers/print', [CustomerController::class, 'print'])->name('customers.print');

Route::resource('countries', CountryController::class)->only('index', 'edit', 'update');
Route::post('countries/{country}/toggle', [CountryController::class, 'toggleActive'])->name(
	'countries.toggle-active'
);

Route::post('countries/change-admin-country-id', [CountryController::class, 'changeAdminCountryId'])->name(
	'countries.change-admin-country-id'
);

Route::resource('clubs', ClubController::class)->except('show');
Route::post('clubs/{club}/login', [ClubController::class, 'login'])->name('clubs.login');
Route::post('clubs/{club}/fields/{field}/toggle', [ClubController::class, 'toggleField'])->name(
	'clubs.toggle-field'
);

Route::post('clubs/{club}/fields/{field}/set', [ClubController::class, 'setField'])->name('clubs.set-field');

Route::post('clubs/{club}/payment-method', [ClubPaymentMethodController::class, 'paymentMethodSelect'])->name(
	'clubs.payment-method-select'
);
Route::post('clubs/{club}/payment-method/{payment_method}', [
	ClubPaymentMethodController::class,
	'paymentMethodConnect',
])->name('clubs.payment-method-connect');
Route::delete('clubs/{club}/payment-method/{payment_method}', [
	ClubPaymentMethodController::class,
	'paymentMethodDisconnect',
])->name('clubs.payment-method-disconnect');
Route::put('clubs/{club}/payment-method/{payment_method}/field/{field}', [
	ClubPaymentMethodController::class,
	'update',
])->name('clubs.payment-method-update');

Route::match(['post', 'put'], '/clubs/{club}/settings/{key}', [ClubController::class, 'updateSetting'])->name(
	'clubs.settings.update'
);
Route::post('/club/{club}/games/{game}', [ClubController::class, 'updateClubGameData'])->name(
	'clubs.games.update'
);
Route::put('/club/{club}/games/{game_club}/commissions', [
	ClubController::class,
	'updateGameCommission',
])->name('clubs.game-commissions.update');

Route::post('clubs/{club}/games/{game}/move/{moveType}', [ClubController::class, 'moveGame'])->name(
	'clubs.move-game'
);
Route::post('clubs/{club}/games/{game}/toggle/', [ClubController::class, 'toggleGame'])->name(
	'clubs.toggle-game'
);

Route::post('clubs/{club}/products/{product}', [ClubController::class, 'addProduct'])->name(
	'clubs.product.add'
);
Route::delete('clubs/{club}/products/{product_club}', [ClubController::class, 'removeProduct'])->name(
	'clubs.product.remove'
);
Route::put('clubs/{club}/products/{product_club}', [ClubController::class, 'editProduct'])->name(
	'clubs.product.update'
);

Route::resource('games', GameController::class)->except('show', 'update');
Route::post('/games/{game}', [GameController::class, 'update'])->name('games.update');
Route::resource('games.features', FeatureController::class)->only('store', 'destroy');
Route::post('games/{game}/features/{feature}', [FeatureController::class, 'update'])->name(
	'games.features.update'
);

Route::get('/translations', [TranslationController::class, 'index'])->name('translations.index');
Route::put('/translations', [TranslationController::class, 'update'])->name('translations.update');
Route::post('/translations/copy', [TranslationController::class, 'copy'])->name('translations.copy');

Route::resource('reservations', ReservationController::class)->only('index');
Route::get('/reservations/export', [ReservationController::class, 'export'])->name('reservations.export');

Route::get('/refunds', [RefundController::class, 'index'])->name('refunds.index');
Route::get('/refunds/{refund}/approve', [RefundController::class, 'approve'])->name('refunds.approve');

Route::prefix('settlements/clubs')->group(function () {
	Route::get('', [SettlementController::class, 'index'])->name('settlements.index');

	Route::prefix('{club}')->group(function () {
		Route::get('', [SettlementController::class, 'showClub'])->name('settlements.show-club');

		Route::prefix('/show/{settlement}')->group(function () {
			Route::get('', [SettlementController::class, 'show'])->name('settlements.show');
			Route::get('/download', [SettlementController::class, 'invoiceDownload'])->name(
				'settlements.invoice-download'
			);

			Route::prefix('/game/{game}')->group(function () {
				Route::get('/download', [SettlementController::class, 'export'])->name(
					'settlements.club-game-download'
				);
				Route::get('/reservations', [SettlementController::class, 'showReservations'])->name(
					'settlements.club-reservations'
				);
			});
		});
	});
});

Route::resource('help-sections', HelpSectionController::class)->only(
	'index',
	'create',
	'store',
	'edit',
	'update',
	'destroy'
);
Route::post('help-sections/{help_section}/toggle', [HelpSectionController::class, 'toggleActive'])->name(
	'help-sections.toggle-active'
);
Route::post('/help-sections/copy', [HelpSectionController::class, 'copy'])->name('help-sections.copy');

Route::scopeBindings()->group(static function () {
	Route::resource('help-sections.help-items', HelpItemController::class)->only(
		'index',
		'create',
		'store',
		'edit',
		'destroy'
	);
	Route::post('/help-sections/{help_section}/help_items/{help_item}', [
		HelpItemController::class,
		'update',
	])->name('help-sections.help-items.update');
	Route::post('help-sections/{help_section}/help-items/{help_item}/toggle', [
		HelpItemController::class,
		'toggleActive',
	])->name('help.sections.help-items.toggle-active');
	Route::resource('help-sections.help-items.help-item-images', HelpItemImageController::class)->only(
		'store',
		'destroy'
	);
});

Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::match(['put', 'post'], '/settings/{key}', [SettingController::class, 'update'])->name(
	'settings.update'
);
Route::put('/settings/payment-methods/{payment_method}/field/{field}', [
	SettingController::class,
	'updatePaymentMethod',
])->name('settings.payment-methods.update');

Route::get('/statistics', [StatisticController::class, 'index'])->name('statistics.index');
Route::get('/statistics/export', [StatisticController::class, 'export'])->name('statistics.export');

Route::resource('products', ProductController::class)->only(
	'index',
	'create',
	'store',
	'edit',
	'update',
	'destroy'
);
