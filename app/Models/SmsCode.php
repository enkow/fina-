<?php

namespace App\Models;

use App\Enums\SmsCodeType;
use App\Traits\Searchable;
use App\Traits\Sortable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SmsCode extends BaseModel
{
	use Searchable, Sortable;

	public static array $loggable = ['customer_id', 'type', 'code', 'active', 'expired_at'];

	protected $fillable = ['customer_id', 'type', 'code', 'active', 'expired_at'];

	protected $casts = [
		'type' => SmsCodeType::class,
		'active' => 'boolean',
		'expired_at' => 'datetime',
	];

	protected static function boot()
	{
		parent::boot();

		static::creating(function ($model) {
			$code_temp = (string) random_int(1, 999999);
			$code_temp = str_pad($code_temp, 6, '0', STR_PAD_LEFT);

			$model->code = $code_temp;

			//			if ($model->expired_at === null) {
			//				$model->setExpirationTimeInMinutes(3);
			//			}
		});
	}

	public function customer(): HasOne
	{
		return $this->hasOne(Customer::class, 'id', 'customer_id');
	}

	//	public function setExpirationTimeInMinutes(int $minutes)
	//	{
	//		$this->expired_at = Carbon::now()->addMinutes($minutes);
	//	}
}
