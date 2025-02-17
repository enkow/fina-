<?php

namespace App\Models;

use App\Models\PaymentMethods\Stripe;
use App\Models\PaymentMethods\Tpay;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Parental;
use RuntimeException;

class PaymentMethod extends BaseModel
{
	use SoftDeletes, Parental\HasChildren;

	public const ADMIN_TAB = null;
	public const CHILD_TYPES = [
		'stripe' => Stripe::class,
		'tpay' => Tpay::class,
	];

	protected $guarded = ['id'];
	protected $casts = [
		'activated' => 'boolean',
		'active' => 'boolean',
		'online' => 'boolean',
		'enabled' => 'boolean',
		'credentials' => 'encrypted:object',
	];

	public static function getPaymentMethod(int $paymentMethodId)
	{
		return Cache::remember(
			'paymentMethod:' . $paymentMethodId,
			config('cache.model_cache_time'),
			function () use ($paymentMethodId) {
				return self::where('id', $paymentMethodId)->first();
			}
		);
	}

	public static function disabledAllOnlineMethod(Club $club)
	{
		PaymentMethod::where('club_id', '=', $club->id)->update(['enabled' => false]);
	}

	public static function enable(Club $club)
	{
		throw new RuntimeException('Method enable() must be implemented in a child class');
	}

	public static function connect(Request $request, Club $club)
	{
		throw new RuntimeException('Method connect() must be implemented in a child class');
	}

	public static function return(Request $request, Club $club)
	{
		throw new RuntimeException('Method return() must be implemented in a child class');
	}

	public function flushCache(): void
	{
		Cache::forget('paymentMethod:' . $this->id);
	}

	public function country(): HasOne
	{
		return $this->hasOne(Country::class);
	}

	public function reservations(): HasMany
	{
		return $this->hasMany(Reservation::class);
	}

	public function club(): BelongsTo
	{
		return $this->belongsTo(Club::class);
	}

	public function getChildTypes(): array
	{
		return self::CHILD_TYPES ?? [];
	}

	public function getChildType()
	{
		return self::CHILD_TYPES[$this->type] ?? null;
	}

	public function createPaymentUrl(Payment $payment, string $return_url): string
	{
		throw new RuntimeException('Method createPaymentUrl() must be implemented in a child class');
	}

	public function refundPayment(Payment $payment, int $amount): array
	{
		throw new RuntimeException('Method refundPayment() must be implemented in a child class');
	}

	public function calcProviderCommission(Payment $payment): int
	{
		if (
			$payment->paymentMethod->fee_percentage === 0 &&
			$payment->paymentMethod->fee_fixed === 0 &&
			!($payment->payable instanceof Reservation)
		) {
			return 0;
		}

		$clubPaymentMethod = null;

		if ($payment->paymentMethod->fee_percentage !== 0 || $payment->paymentMethod->fee_fixed !== 0) {
			$clubPaymentMethod = $payment->paymentMethod;
		} else {
			$clubPaymentMethod =
				$payment->payable->firstReservationSlot->slot->pricelist->club->paymentMethod;
		}

		return (int) round(
			($payment->total / 10000) * $clubPaymentMethod->fee_percentage + $clubPaymentMethod->fee_fixed,
			0,
			PHP_ROUND_HALF_UP
		);
	}
}
