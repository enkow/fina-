<?php

use App\Http\Controllers\Club\AnnouncementController;
use App\Http\Controllers\Club\BillingController;
use App\Http\Controllers\Club\CustomerController;
use App\Http\Controllers\Club\DiscountCodeController;
use App\Http\Controllers\Club\EmployeeController;
use App\Http\Controllers\Club\HelpItemController;
use App\Http\Controllers\Club\HelpSectionController;
use App\Http\Controllers\Club\ManagerEmailController;
use App\Http\Controllers\Club\OnlinePaymentController;
use App\Http\Controllers\Club\OpeningHoursController;
use App\Http\Controllers\Club\OpeningHoursExceptionController;
use App\Http\Controllers\Club\PricelistController;
use App\Http\Controllers\Club\PricelistExceptionController;
use App\Http\Controllers\Club\RateController;
use App\Http\Controllers\Club\ReservationController;
use App\Http\Controllers\Club\ReservationTypeController;
use App\Http\Controllers\Club\SetController;
use App\Http\Controllers\Club\SettingController;
use App\Http\Controllers\Club\SettlementController;
use App\Http\Controllers\Club\SlotController;
use App\Http\Controllers\Club\SpecialOfferController;
use App\Http\Controllers\Club\StatisticController;
use App\Http\Controllers\Club\AgreementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Club\RefundController;
use App\Http\Controllers\Club\InvoiceController;

Route::controller(ReservationController::class)
	->prefix('/reservations')
	->group(function () {
		Route::get('/', 'index')->name('reservations.index');
		Route::get('/export/{canceled?}', 'export')->name('reservations.export');
		Route::get('/calculate-price', 'calculatePrice')->name('reservations.calculate-price');
		Route::post('/cancel', 'cancel')->name('reservations.cancel');
		Route::get('/{reservationNumber}/history', 'history')->name('reservations.history');
		Route::post('/{reservationNumber}/update', 'update')->name('reservations.update');
		Route::patch('/{reservationNumber}/update/feature', 'featureUpdate')->name(
			'reservations.feature-update'
		);
		Route::post('/{reservationNumber}/toggle-presence', 'togglePresence')->name(
			'reservations.toggle-presence'
		);
		Route::get('/{reservationNumber}/start-timer', 'startTimer')->name('reservations.start-timer');
		Route::get('/{reservationNumber}/stop-timer', 'stopTimer')->name('reservations.stop-timer');
		Route::get('/{reservationNumber}/pause-timer', 'pauseTimer')->name('reservations.pause-timer');
		Route::get('/{reservationNumber}/toggle-occupied-status', 'toggleOccupiedStatus')->name(
			'reservations.toggle-occupied-status'
		);
	});

Route::prefix('/games/{game:name}/reservations')
	->controller(ReservationController::class)
	->group(function () {
		Route::middleware('throttle:10,1')->group(function () {
			Route::post('/store', 'store')->name('games.reservations.store');
		});
		Route::get('/calendar', 'calendar')->name('games.reservations.calendar');
		Route::get('/search', 'search')->name('games.reservations.search');
	});

Route::resource('reservation-types', ReservationTypeController::class)->only(
	'index',
	'store',
	'update',
	'destroy'
);

Route::scopeBindings()->group(function () {
	Route::get('/games/{game}/slots/search', [SlotController::class, 'search'])->name('games.slots.search');

	Route::post('/games/{game}/slots/{slot}/toggle-active', [SlotController::class, 'toggleActive'])->name(
		'games.slots.toggle-active'
	);

	Route::put('/games/{game}/slots/{slot}/change-bulb', [ReservationController::class, 'changeBulb'])->name(
		'games.slots.change-bulb'
	);

	Route::post('/games/{game}/slots/{slot}/toggle-online-status', [
		SlotController::class,
		'toggleOnlineStatus',
	])->name('games.slots.toggle-online-status');

	Route::resource('games.slots', SlotController::class)->except('show');

	Route::resource('games.pricelists', PricelistController::class)->except('show');

	Route::resource('games.pricelists.pricelist-exceptions', PricelistExceptionController::class)->only(
		'store'
	);
});

Route::controller(StatisticController::class)
	->prefix('/games/{game}/statistics')
	->group(function () {
		Route::get('main', 'main')->name('games.statistics.main');
		Route::get('main/export', 'mainExport')->name('games.statistics.main.export');
		Route::get('weekly', 'weekly')->name('games.statistics.weekly');
	})
	->middleware(['isUserType:manager']);

Route::get('/statistics/sets', [StatisticController::class, 'sets'])
	->name('statistics.sets')
	->middleware(['isUserType:manager', 'isSetsEnabled']);

Route::resource('special-offers', SpecialOfferController::class)->except('show', 'update');
Route::post('/special-offers/{special_offer}', [SpecialOfferController::class, 'update'])->name(
	'special-offers.update'
);
Route::post('/special-offers/{special_offer}/toggle-active', [
	SpecialOfferController::class,
	'toggleActive',
])->name('special-offers.toggle-active');
Route::get('/special-offers/{special_offer}/clone', [SpecialOfferController::class, 'clone'])->name(
	'special-offers.clone'
);

Route::resource('discount-codes', DiscountCodeController::class)->except('show');
Route::post('/discount-codes/{discount_code}/toggle-active', [
	DiscountCodeController::class,
	'toggleActive',
])->name('discount-codes.toggle-active');
Route::get('/discount-codes/{discount_code}/clone', [DiscountCodeController::class, 'clone'])->name(
	'discount-codes.clone'
);

Route::group(['middleware' => 'isSetsEnabled'], function () {
	Route::resource('sets', SetController::class)->except('show', 'update');
	Route::post('/sets/{set}', [SetController::class, 'update'])->name('sets.update');
	Route::post('sets/{set}/toggle', [SetController::class, 'toggleActive'])->name('sets.toggle-active');
});

Route::controller(SettingController::class)
	->prefix('settings')
	->group(function () {
		Route::get('reservation', 'reservation')->name('settings.reservation');
		Route::get('calendar', 'calendar')->name('settings.calendar');
		Route::match(['put', 'post'], '{key}', 'update')->name('settings.update');
	})
	->middleware('isUserType:manager,admin');

Route::resource('manager-mails', ManagerEmailController::class)->only('store', 'destroy');

Route::resource('agreements', AgreementController::class)->except(['create', 'store', 'update']);
Route::post('/agreements/{agreement}/update', [AgreementController::class, 'update'])->name(
	'agreements.update'
);
Route::post('/agreements/{agreement}/toggle-active', [AgreementController::class, 'toggleActive'])->name(
	'agreements.toggleActive'
);
Route::post('/agreements/{agreement}/toggle-required', [AgreementController::class, 'toggleRequired'])->name(
	'agreements.toggleRequired'
);

Route::singleton('opening-hours', OpeningHoursController::class)->only('show', 'update');
Route::resource('opening-hours-exceptions', OpeningHoursExceptionController::class)->only(
	'create',
	'store',
	'edit',
	'update',
	'destroy'
);

Route::get('online-payments', [OnlinePaymentController::class, 'index'])->name('online-payments.index');
Route::get('online-payments/{type}/connect', [OnlinePaymentController::class, 'connect'])->name(
	'online-payments.connect'
);
Route::get('online-payments/{type}/disconnect', [OnlinePaymentController::class, 'disconnect'])->name(
	'online-payments.disconnect'
);
Route::get('online-payments/{type}/return', [OnlinePaymentController::class, 'return'])->name(
	'online-payments.return'
);

Route::get('customers/search', [CustomerController::class, 'search'])->name('customers.search');
Route::get('/customers/export/', [CustomerController::class, 'export'])
	->name('customers.export')
	->middleware('isUserType:manager');
Route::resource('customers', CustomerController::class)
	->except('edit')
	->middleware('isUserType:manager');
Route::delete('customers/{customer}/tags/{tag}/detach', [CustomerController::class, 'detachTag'])
	->name('customers.detach-tag')
	->scopeBindings();
Route::put('customers/{customer}/tags', [CustomerController::class, 'updateTags'])->name(
	'customers.update-tags'
);
Route::get('customers/{customer}/reservations/export', [
	CustomerController::class,
	'exportReservations',
])->name('customers.reservations.export');
Route::post('customers/{customer}/toggle-consent/{agreementType}', [
	CustomerController::class,
	'toggleConsent',
])->name('customers.toggleConsent');

Route::resource('announcements', AnnouncementController::class)->only(
	'index',
	'store',
	'edit',
	'update',
	'destroy'
);

Route::resource('help-sections', HelpSectionController::class)->only(['index', 'show']);

Route::get('/help-section/{helpSection}/help-items/search', [HelpItemController::class, 'search'])->name(
	'help-sections.help-items.search'
);
Route::scopeBindings()->group(function () {
	Route::resource('help-sections.help-items', HelpItemController::class)->only(['show']);
});

Route::name('billing.')
	->prefix('/billing')
	->group(function () {
		Route::get('/', [BillingController::class, 'index'])->name('index');
		Route::post('/update-details', [BillingController::class, 'updateDetails'])->name('update-details');
		Route::get('/add-method', [BillingController::class, 'addMethod'])->name('add-method');
		Route::post('/select-method', [BillingController::class, 'selectMethod'])->name('select-method');
		Route::delete('/remove-method', [BillingController::class, 'removeMethod'])->name('remove-method');

		Route::name('subscription.')
			->prefix('/subscription')
			->group(function () {
				Route::post('/', [BillingController::class, 'activateSubscription'])->name('activate');
				Route::delete('/', [BillingController::class, 'cancelSubscription'])->name('cancel');
			});
	});
Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
Route::get('/invoices/{invoice}/export', [InvoiceController::class, 'export'])->name('invoices.export');

Route::resource('/rates', RateController::class)->only('index');
Route::get('/export', [RateController::class, 'export'])->name('rates.export');

Route::resource('employees', EmployeeController::class)
	->except('show')
	->middleware('isUserType:manager');

Route::get('/settlements/{settlement}/game/{game}', [SettlementController::class, 'showReservations'])
	->name('settlements.reservations')
	->middleware('isUserType:manager');
Route::get('/settlements/{settlement}/game/{game}/download', [SettlementController::class, 'export'])
	->name('settlements.export')
	->middleware('isUserType:manager');
Route::resource('settlements', SettlementController::class)
	->only('index', 'show')
	->middleware('isUserType:manager');

Route::get('/refunds', [RefundController::class, 'index'])->name('refunds.index');
Route::get('/refunds/{refund}/approve', [RefundController::class, 'approve'])->name('refunds.approve');

//Route::get('/dashboard',[DashboardController::class,'index'])->name('user.dashboard');
//Route::get('/calendar',[DashboardController::class,'calendar'])->name('user.calendar');
//Route::get('/statistics',[DashboardController::class,'statistics'])->name('user.statistics');
//Route::get('/tables',[DashboardController::class,'tables'])->name('user.tables');
//Route::get('/videos',[DashboardController::class,'videos'])->name('user.videos');
//Route::get('/videos/single',[DashboardController::class,'videosSingle'])->name('user.videos.single');
//Route::get('/pagination',[DashboardController::class,'pagination'])->name('user.pagination');
//Route::get('/helpBoxes',[DashboardController::class,'helpBoxes'])->name('user.help-boxes');
//Route::get('/settlements',[DashboardController::class,'settlements'])->name('user.settlements');
