<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Middleware\AuthenticateApiSync;

use App\Http\Controllers\Api\Migrate\ClubController;
use App\Http\Controllers\Api\Migrate\EmployeeController;
use App\Http\Controllers\Api\Migrate\SetController;
use App\Http\Controllers\Api\Migrate\OpeningHourController;
use App\Http\Controllers\Api\Migrate\OpeningHourExController;
use App\Http\Controllers\Api\Migrate\CodeController;
use App\Http\Controllers\Api\Migrate\SettingsController;
use App\Http\Controllers\Api\Migrate\PricelistController;
use App\Http\Controllers\Api\Migrate\PricelistItemController;
use App\Http\Controllers\Api\Migrate\PricelistExceptionsController;
use App\Http\Controllers\Api\Migrate\DiscountController;
use App\Http\Controllers\Api\Migrate\AnnouncementsController;
use App\Http\Controllers\Api\Migrate\AdminSettingsController;
use App\Http\Controllers\Api\Migrate\AgreementController;
use App\Http\Controllers\Api\Migrate\HallTablesController;
use App\Http\Controllers\Api\Migrate\ReservationTypeController;
use App\Http\Controllers\Api\Migrate\ResourceController;
use App\Http\Controllers\Api\Migrate\UserController;
use App\Http\Controllers\Api\Migrate\PricelistTableController;
use App\Http\Controllers\Api\Migrate\ReservationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/online-payments/{type}/webhook', [
	\App\Http\Controllers\Api\OnlinePaymentController::class,
	'webhook',
])->name('online-payments.webhook');

Route::prefix('/migrate')
	->middleware([AuthenticateApiSync::class])
	->group(function () {
		Route::controller(ClubController::class)->group(function () {
			Route::post('/club', 'store');
			Route::put('/club/{old_id}', 'update');
			Route::delete('/club/{old_id}', 'destroy');
		});

		Route::controller(EmployeeController::class)->group(function () {
			Route::post('/employee', 'store');
			Route::put('/employee/{old_id}', 'update');
			Route::delete('/employee/{old_id}', 'destroy');
		});

		Route::controller(SetController::class)->group(function () {
			Route::post('/set', 'store');
			Route::put('/set/{old_id}', 'update');
			Route::delete('/set/{old_id}', 'destroy');
		});

		Route::controller(OpeningHourController::class)->group(function () {
			Route::post('/openingHour', 'store');
			Route::put('/openingHour/{old_id}', 'update');
			Route::delete('/openingHour/{old_id}', 'destroy');
		});

		Route::controller(OpeningHourExController::class)->group(function () {
			Route::post('/openingHourEx', 'store');
			Route::put('/openingHourEx/{old_id}', 'update');
			Route::delete('/openingHourEx/{old_id}', 'destroy');
		});

		Route::controller(CodeController::class)->group(function () {
			Route::post('/code', 'store');
			Route::put('/code/{old_id}', 'update');
			Route::delete('/code/{old_id}', 'destroy');
		});

		Route::controller(AdminSettingsController::class)->group(function () {
			Route::post('/clubAdminSettings', 'store');
			Route::put('/clubAdminSettings/{old_id}', 'update');
			Route::delete('/clubAdminSettings/{old_id}', 'destroy');
		});

		Route::controller(SettingsController::class)->group(function () {
			Route::post('/clubSettings', 'store');
			Route::put('/clubSettings/{old_id}', 'update');
			Route::delete('/clubSettings/{old_id}', 'destroy');
		});

		Route::controller(PricelistController::class)->group(function () {
			Route::post('/pricelist', 'store');
			Route::put('/pricelist/{old_id}', 'update');
			Route::delete('/pricelist/{old_id}', 'destroy');
		});

		Route::controller(PricelistItemController::class)->group(function () {
			Route::post('/pricelistItem', 'store');
			Route::put('/pricelistItem/{old_id}', 'update');
			Route::delete('/pricelistItem/{old_id}', 'destroy');
		});

		Route::controller(PricelistExceptionsController::class)->group(function () {
			Route::post('/pricelistException', 'store');
			Route::put('/pricelistException/{old_id}', 'update');
			Route::delete('/pricelistException/{old_id}', 'destroy');
		});

		Route::controller(DiscountController::class)->group(function () {
			Route::post('/discount', 'store');
			Route::put('/discount/{old_id}', 'update');
			Route::delete('/discount/{old_id}', 'destroy');
		});

		Route::controller(AnnouncementsController::class)->group(function () {
			Route::post('/announcements', 'store');
			Route::put('/announcements/{old_id}', 'update');
			Route::delete('/announcements/{old_id}', 'destroy');
		});

		Route::controller(ReservationTypeController::class)->group(function () {
			Route::post('/reservationKind', 'store');
			Route::put('/reservationKind/{old_id}', 'update');
			Route::delete('/reservationKind/{old_id}', 'destroy');
		});

		Route::controller(AgreementController::class)->group(function () {
			Route::put('/terms/{old_id}', 'update');
			Route::delete('/terms/{old_id}', 'destroy');
		});

		Route::controller(ResourceController::class)->group(function () {
			Route::post('/resource', 'store');
			Route::put('/resource/{old_id}', 'update');
			Route::delete('/resource/{old_id}', 'destroy');
		});

		Route::controller(PricelistTableController::class)->group(function () {
			Route::post('/pricelistTable', 'store');
			Route::put('/pricelistTable/{old_id}', 'update');
			Route::delete('/pricelistTable/{old_id}', 'destroy');
		});

		Route::controller(UserController::class)->group(function () {
			Route::post('/customer', 'store');
			Route::post('/customer/group', 'group');
			Route::put('/customer/{old_id}', 'update');
			Route::delete('/customer/{old_id}', 'destroy');
		});

		Route::controller(ReservationController::class)->group(function () {
			Route::post('/reservation', 'store');
			Route::post('/reservation/group', 'group');
			Route::put('/reservation/{old_id}', 'update');
			Route::delete('/reservation/{old_id}', 'destroy');
		});

		Route::controller(HallTablesController::class)->group(function () {
			Route::post('/clubHall', 'store');
			Route::put('/clubHall/{old_id}', 'update');
			Route::delete('/clubHall/{old_id}', 'destroy');
		});
	});
