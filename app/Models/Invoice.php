<?php

namespace App\Models;

use App\Exceptions\MissingBillingDetailsException;
use App\Exceptions\MissingPaymentMethodException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\AnonymousNotifiable;
use Stripe\PaymentIntent;
use App\Notifications\UnpaidInvoiceNotification;
use App\Custom\Fakturownia;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Invoice extends Model
{
	use HasRelationships;

	protected $guarded = ['id'];

	protected $fillable = [
		'title',
		'fakturownia_id',
		'fakturownia_token',
		'total',
		'currency',
		'vat',
		'auto_send',
		'advance_payment',
		'fee_year',
		'fee_month',
		'days_for_payment',
		'last',
		'paid_at',
		'sent_at',
		'stripe_payment_intent_id',
		'to',
		'from',
	];

	protected $casts = [
		'paid_at' => 'timestamp',
		'total' => 'integer',
	];

	public function items()
	{
		return $this->hasMany(InvoiceItem::class);
	}

	public function payments(): MorphMany
	{
		return $this->morphMany(Payment::class, 'payable');
	}

	public function club(): BelongsTo
	{
		return $this->belongsTo(Club::class);
	}

	public function reservations(): HasMany
	{
		return $this->hasMany(Reservation::class);
	}

	public function reservationsSlots(): HasManyDeep
	{
		return $this->hasManyDeep(
			ReservationSlot::class,
			[Reservation::class],
			['invoice_id', 'reservation_id'],
			['id', 'id']
		);
	}

	public function charge(bool $ignoreDisabledSubscription = false): void
	{
		$this->loadMissing(['club']);
		if (
			blank($this->club->stripe_customer_id) ||
			(!$this->club->subscription_active && !$ignoreDisabledSubscription)
		) {
			$this->fail();
			return;
			//			throw new MissingBillingDetailsException();
		}

		$customer = \Stripe\Customer::retrieve($this->club->stripe_customer_id);
		$paymentMethod = $customer->invoice_settings->default_payment_method;

		if (blank($paymentMethod)) {
			info('Missing payment method');
			$this->fail();
			return;
			//			throw new MissingPaymentMethodException();
		}

        $intent = PaymentIntent::create([
            'amount' => $this->total,
            'currency' => $this->currency,
            'confirm' => true,
            'customer' => $this->club->stripe_customer_id,
            'off_session' => true,
            'payment_method' => $paymentMethod,
            'metadata' => [
                'invoice_id' => $this->id,
            ],
        ]);

		$this->update([
			'stripe_payment_intent_id' => $intent['id'],
		]);
		info('Payment intent: ' . $intent['id']);

		if ($intent['amount_received'] === $intent['amount']) {
			$this->success();
		}
		info(
			'Payment intent amount received: ' . $intent['amount_received'] . ' amount: ' . $intent['amount']
		);
	}

	public function success(): void
	{
		$this->update([
			'paid_at' => now(),
		]);
		$this->club->update([
			'invoice_next_month' => !$this->invoice_last
				? now()
					->addMonths(1)
					->format('Y-m-d')
				: null,
			'subscription_active' => $this->club->invoice_autopay && !$this->club->invoice_last,
			'preview_mode' => false,
		]);
		$this->fakturowniaCreate();
	}

	public function fail(): void
	{
		// Payment didn't go through: send an email + change the club mode
		// Sends a notification to the site administrator

		$notifiable = (new AnonymousNotifiable())->route('mail', config('fakturownia.admin_mail'));
		$notifiable->notify(new UnpaidInvoiceNotification($this->club));

		// Sends notification to all managers
		foreach ($this->club->managers as $manager) {
			$manager->notify(new UnpaidInvoiceNotification($this->club));
		}

		// Enable preview mode in case of non-payment
		$this->club->preview_mode = true;
		$this->club->invoice_next_month = null;
		$this->club->save();
	}

	public function fakturowniaCreate()
	{
		// Preparing translations for game commissions
		$translationCommissions = [];
		foreach (['pl', 'gb'] as $code) {
			$country = Country::where('code', $code)->first();
			$commissionPrefix = trans('invoice.commission', [], $country->locale);

			$translationsGame = Translation::gamesTranslationsArray($country->id);

			foreach ($translationsGame as $key => $translation) {
				$translationCommissions[$country->locale][$key] =
					$commissionPrefix . ' ' . $translation['game-name'];
			}
		}

		$translationGames = $translationCommissions[$this->club->invoice_lang];

		// Object needed to create an invoice in Fakturownia
		$fakturowniaInvoice = [
            'payment_to_kind' => 'off',
			'client_id' => $this->club->fakturownia_id,
			'department_id' => $this->club->country->fakturownia_id,
			'currency' => $this->currency,
			'lang' => $this->club->invoice_lang,
			'positions' => [],
		];
        if(!$this->club->invoice_autopay) {
            $fakturowniaInvoice['payment_to_kind'] = $this->days_for_payment;
        }

		if ($this->paid_at !== null) {
			$fakturowniaInvoice['status'] = 'paid';
			$fakturowniaInvoice['payment_type'] = 'card';
		}

		foreach ($this->items as $item) {
			if ($item->total === 0) {
				continue;
			}

			$fakturowniaPos = &$fakturowniaInvoice['positions'][];

			$fakturowniaPos = [
				'quantity' => $item->settings['quantity'] ?? 1,
				'tax' => $this->vat,
				'total_price_gross' =>
					($item->total + $item->total * ((is_numeric($this->vat) ? $this->vat : 0) / 100)) / 100,
			];

			if ($item->model->getMorphClass() === 'App\\Models\\Game') {
				$fakturowniaPos['name'] = $translationGames[$item->model->id];
			} else {
				$fakturowniaPos['name'] =
					$this->club->invoice_lang === 'pl' ? $item->model->name_pl : $item->model->name_en;
			}
		}

        info(json_encode($fakturowniaInvoice));

		$fakturownia = new Fakturownia();
		// Creating an invoice in Fakturownia
		$fakturowniaInvoiceResult = $fakturownia->createInvoice(['invoice' => $fakturowniaInvoice]);
		$invoiceId = $fakturowniaInvoiceResult['id'];

		// Adding invoice information to the invoice model; this information is needed later in the club panel to retrieve the invoice
		$this['fakturownia_id'] = $invoiceId;
		$this['fakturownia_token'] = $fakturowniaInvoiceResult['token'];
		$this['title'] = $fakturowniaInvoiceResult['number'];

		$this->save();

		if ($this->club->invoice_autosend) {
			$fakturownia->sendInvoiceToClient($invoiceId);
		}

		return $this->items[0]->model->getMorphClass();
	}
}
