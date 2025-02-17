<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Club\ReservationController;
use App\Http\Controllers\TablePreferenceController;
use App\Http\Controllers\Widget\CustomerController;
use App\Http\Controllers\Widget\DiscountCodeController;
use App\Http\Controllers\Widget\SetController;
use App\Http\Controllers\Widget\WidgetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
	return view('errors/502');
});

Route::redirect('/login', '/panel');
Route::get('/panel', [\Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::class, 'create'])
	->middleware(['guest'])
	->name('login');

Route::post('/panel', [
	\Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::class,
	'store',
])->middleware(['guest']);

Route::get('/dashboard', function () {
	$routeName = match (auth()->user()->type) {
		'admin' => 'admin.countries.index',
		'manager', 'employee' => 'club.games.reservations.calendar',
	};
	if (
		club()
			?->games()
			->count() === 0
	) {
		\Illuminate\Support\Facades\Auth::logout();
	}
	return redirect()->route($routeName, [
		'game' => club()
			?->games()
			->first(),
	]);
})->name('dashboard-redirect');

Route::post('/toggle-sidebar-reduction-status', static function () {
	$isSidebarReduced = auth()->user()->sidebar_reduced;
	auth()
		->user()
		?->update([
			'sidebar_reduced' => ($isSidebarReduced = !$isSidebarReduced),
		]);
	auth()
		->user()
		?->flushCache();

	return ['sidebar_reduced' => auth()->user()->sidebar_reduced];
})->name('global.toggle-sidebar-reduction-status');

Route::post('/hide-first-login-message', static function () {
	auth()
		->user()
		?->club()
		?->update([
			'first_login_message_showed' => true,
		]);
	auth()
		->user()
		?->flushCache();
	return new \App\Http\Resources\UserResource(auth()->user());
})->name('global.hide-first-login-message');

Route::get('/k/find', function () {
	$club = \App\Models\Club::first();

	return redirect('/k/' . $club->slug);
});
Route::get('/k/{club:slug}', [WidgetController::class, 'calendar'])->name('widget.calendar');

Route::get('/new-widget/find', function () {
	$club = \App\Models\Club::first();

	return redirect('/new-widget/' . $club->slug);
});
Route::get('/new-widget/{club:slug}', [WidgetController::class, 'newWidget'])->name('widget.new-widget');

Route::get('/{club:slug}', [WidgetController::class, 'index'])
	->name('widget.index')
	->where('club:slug', "^[^/\\]*$");

Route::prefix('/w/{club:slug}')
	->controller(WidgetController::class)
	->group(function () {
		Route::get('/', 'index')->name('widget.display');
		Route::post('/set-country/{locale}', 'setCountryByLocaleCode')->name('widget.set-country');
		Route::get('/start-at-dates', 'getAvailableStartAtDates')->name('widget.start-at-dates');
		Route::get('/opening-hours', 'getClubOpeningHoursForDate')->name('widget.opening-hours');
		Route::get('/price', 'calculatePrice')->name('widget.calculate-price');
		Route::post('/store', 'store')->name('widget.store');
		Route::get('/payment-status', 'paymentProcessEnded')->name('widget.payment-status');

		/*
		 * Widget customer authentication routes
		 */
		Route::post('/customers/register', [CustomerController::class, 'register'])->name(
			'widget.customers.register'
		);
		Route::post('/customers/login', [CustomerController::class, 'login'])->name('widget.customers.login');
		Route::post('/customers/consents', [CustomerController::class, 'updateConsents'])->name(
			'widget.customers.update-consents'
		);
		Route::put('/customer/phone', [CustomerController::class, 'updatePhoneNumber'])->name(
			'widget.customers.update-phone'
		);
		Route::post('/customers/logout', [CustomerController::class, 'logout'])->name(
			'widget.customers.logout'
		);
		Route::post('/customers/forgot-password', [CustomerController::class, 'forgotPassword'])->name(
			'widget.customers.forgot-password'
		);
		Route::get('/customers/{customer}/password-recovery/{token}', [
			CustomerController::class,
			'passwordRecovery',
		])->name('widget.customers.password-recovery');
		Route::post('/customers/{customer}/password-recovery/{token}', [
			CustomerController::class,
			'passwordRecoveryAction',
		])->name('widget.customers.password-recovery-action');
		Route::get('/customers/verification/resend/{encryptedCustomerId?}', [
			CustomerController::class,
			'resendVerification',
		])->name('widget.customers.verification-resend');
		Route::put('/customers/verification/code/{encryptedCustomerId?}', [
			CustomerController::class,
			'smsVerification',
		])->name('widget.customers.sms-verification-code');
		Route::get('/customers/verification/{encryptedCustomerId}', [
			CustomerController::class,
			'emailVerification',
		])->name('widget.customers.email-verification');
		Route::get('/customers/{encryptedCustomerId}', [CustomerController::class, 'show'])->name(
			'widget.customers.show'
		);
		Route::get('/customers/{encryptedCustomerId}/reservationNumber/{reservationNumber}/cancel', [
			\App\Http\Controllers\Widget\ReservationController::class,
			'cancel',
		])->name('widget.customers.reservations.cancel');
		Route::post('/customers/{encryptedCustomerId}/reservationNumber/{reservationNumber}/cancel', [
			\App\Http\Controllers\Widget\ReservationController::class,
			'cancelAction',
		])->name('widget.customers.reservations.cancel-action');

		Route::get('/customers/{encryptedCustomerId}/reservationNumber/{reservationNumber}/rate', [
			\App\Http\Controllers\Widget\ReservationController::class,
			'rating',
		])->name('widget.customers.reservations.rating');
		Route::post('/customers/{encryptedCustomerId}/reservationNumber/{reservationNumber}/rate', [
			\App\Http\Controllers\Widget\ReservationController::class,
			'rate',
		])->name('widget.customers.reservations.rate');
	});
Route::get('/w/{club:slug}/sets', [SetController::class, 'index'])->name('widget.sets');
Route::get('/w/{club:slug}/discount-codes/{discount_code}', [DiscountCodeController::class, 'show'])->name(
	'widget.discount-codes'
);
Route::get('/w/{club:slug}/slots', [\App\Http\Controllers\Widget\SlotController::class, 'index'])->name(
	'widget.slots'
);

Route::get('reservations/{reservationNumber}', [ReservationController::class, 'show'])->name(
	'reservations.show'
);

Route::put('table-preferences/{table_name}', [TablePreferenceController::class, 'update'])->name(
	'table-preferences.update'
);

Route::get('/register/{secret}', [AuthController::class, 'showRegisterForm'])->name('showRegisterForm');
Route::post('/register/{secret}', [AuthController::class, 'register'])->name('register');
