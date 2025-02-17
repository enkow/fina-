<?php

namespace App\Http\Controllers\Widget;

use App\Channels\SmsChannel;
use App\Enums\AgreementType;
use App\Enums\ReminderMethod;
use App\Enums\ReminderType;
use App\Enums\CustomerVerificationMethod;
use App\Events\CustomerLoggedEvent;
use App\Events\CustomerVerifiedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Widget\CustomerForgotPasswordRequest;
use App\Http\Requests\Widget\CustomerPasswordRecoveryRequest;
use App\Http\Requests\Widget\LoginCustomerRequest;
use App\Http\Requests\Widget\RegisterCustomerRequest;
use App\Http\Requests\Widget\UpdateCustomerConsentsRequest;
use App\Http\Requests\Widget\UpdateCustomerPhoneRequest;
use App\Http\Requests\Widget\VerificationCodeRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Club;
use App\Models\Country;
use App\Models\Customer;
use App\Notifications\Customer\PasswordRecoveryNotification;
use App\Notifications\Customer\VerificationNotification;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;

class CustomerController extends Controller
{
	public function register(RegisterCustomerRequest $request, Club $club): JsonResponse
	{
		$widgetCountryCode = session()->get(
			'customCountry',
			Country::getCached()
				->where('code', $club->widget_countries[0] ?? $club->country->code)
				->first()->country
		);
		$widgetCountry = Country::getCached()
			->where('code', $widgetCountryCode)
			->first();
		$request->merge([
			'password' => Hash::make($request->get('password')),
			'widget_channel_expiration' => now('UTC')->addMinutes(30),
			'locale' => $widgetCountry->locale,
		]);

		$customer = $club
			->customers()
			->create(
				$request->only([
					'first_name',
					'last_name',
					'phone',
					'email',
					'password',
					'widget_channel',
					'widget_channel_expiration',
					'locale',
				])
			);

		foreach ($request->get('consents') as $agreementType => $agreementValue) {
			if ($agreementValue) {
				$agreement = $club
					->agreements()
					->where('active', true)
					->where(
						'type',
						match ($agreementType) {
							'general_terms' => AgreementType::GeneralTerms,
							'privacy_policy' => AgreementType::PrivacyPolicy,
							'marketing_agreement' => AgreementType::MarketingAgreement,
						}
					)
					->first();
				$customer->agreements()->attach($agreement);
			}
		}

		$customer->reminders()->create([
			'method' =>
				$club->customer_verification_type === CustomerVerificationMethod::SMS
					? ReminderMethod::Mail
					: ReminderMethod::Sms,
			'type' => ReminderType::RegisterCustomer,
			'real' => 0,
		]);

		$customer->notify(new VerificationNotification());
		session()->put('customer_id', $customer->id);
		$result = [];
		$result['customer'] = new CustomerResource($customer);

		return response()->json($result);
	}

	public function updateConsents(UpdateCustomerConsentsRequest $request, Club $club): RedirectResponse
	{
		$customerId = session()->get('customer_id');
		$customer = Customer::where('id', $customerId)
			->where('club_id', $club->id)
			->first();
		foreach ($request->except('club_slug') as $agreementType => $agreementValue) {
			if ($agreementValue) {
				$agreement = $club
					->agreements()
					->where(
						'type',
						match ($agreementType) {
							'general_terms' => AgreementType::GeneralTerms,
							'privacy_policy' => AgreementType::PrivacyPolicy,
							'marketing_agreement' => AgreementType::MarketingAgreement,
						}
					)
					->whereDoesntHave('customers', function ($query) use ($customerId) {
						$query->where('customers.id', $customerId);
					})
					->first();
				$customer->agreements()->attach($agreement);
			}
		}

		return redirect()->back();
	}

	public function resendVerification(Club $club, string $encryptedCustomerId = null)
	{
		$customer = Customer::where('club_id', $club->id)
			->where('id', session()->get('customer_id', Crypt::decrypt($encryptedCustomerId)))
			->first();
		if (empty($customer) || $customer->verified) {
			return response()->json([
				'error' => true,
				'message' => 'invalid customer',
			]);
		}
		session()->put('customer_id', $customer->id);

		if ($customer->resendVerification()) {
			return response()->json([
				'error' => false,
				'message' => __('widget.verification-sms-resent'),
			]);
		} else {
			return response()->json([
				'error' => true,
				'message' => __('widget.verification-sms-limit-today'),
			]);
		}
	}

	public function emailVerification(Club $club, string $encryptedCustomerId): Response
	{
		$customer = Customer::where('club_id', $club->id)
			->where('id', session()->get('customer_id', Crypt::decrypt($encryptedCustomerId)))
			->first();
		$customer->update([
			'verified' => true,
		]);
		event(new CustomerVerifiedEvent($customer));

		return Inertia::render('Widget-3/EmailVerified', $customer->club->getWidgetProps($customer));
	}

	public function smsVerification(
		VerificationCodeRequest $request,
		Club $club,
		string $encryptedCustomerId = null
	) {
		$code = $request->input('code');
		$customer = Customer::where('club_id', $club->id)
			->where('id', session()->get('customer_id', Crypt::decrypt($encryptedCustomerId)))
			->first();
		if (empty($customer)) {
			return response()->json(['errors' => ['code' => 'validation.invalid']]);
		}
		session()->put('customer_id', $customer->id);

		$validCode = $customer->getValidCode($code);

		if (!$validCode) {
			return response()->json(['errors' => ['code' => 'validation.invalid']]);
		}

		$customer->update([
			'verified' => true,
		]);

		$validCode->update([
			'active' => false,
		]);

		return response()->json([
			'error' => false,
			'customer' => new CustomerResource($customer),
		]);
	}

	public function login(LoginCustomerRequest $request, Club $club): JsonResponse
	{
		$customer = $club
			->customers()
			->where('email', $request->get('email'))
			->where('club_id', $club->id)
			->whereNull('deleted_at')
			->first();
		$result = [];

		$smsSentToday = $customer
			->reminders()
			->where('type', ReminderType::RegisterCustomer)
			->where('method', ReminderMethod::Sms)
            ->where('created_at', '>=', now('UTC')->subHour())
			->count();
		$result['sms_limit_reached'] = ($smsSentToday ?? 0) >= 1;

		if (!empty($customer) && Hash::check($request->get('password'), $customer->password)) {
			session()->put('customer_id', $customer->id);
			$result['customer'] = new CustomerResource($customer);
			$customer->update([
				'widget_channel' => $request->get('widget_channel'),
			]);

			if (!$customer->verified) {
				$customer->resendVerification();
			}
		}

		return response()->json($result);
	}

	public function passwordRecovery(Club $club, Customer $customer, string $token): Response
	{
		return Inertia::render(
			'Widget-3/PasswordRecovery',
			$club->getWidgetProps($customer, [
				'widgetChannel' => $customer->widget_channel,
				'isPasswordChangeAllowed' => $customer->isPasswordRecoveryTokenValid($token),
				'token' => $token,
			])
		);
	}

	public function passwordRecoveryAction(
		CustomerPasswordRecoveryRequest $request,
		Club $club,
		Customer $customer,
		string $token
	): Response|RedirectResponse {
		Customer::where('id', $customer->id)->where('club_id', $club->id)->update([
			'password' => Hash::make($request->get('password')),
			'widget_channel' => $request->get('customer_channel'),
		]);
		event(new CustomerLoggedEvent($customer, $customer->widget_channel));

		return redirect()->back();
	}

	public function logout(Club $club): RedirectResponse
	{
		session()->forget('customer_id');

		return redirect()->back();
	}

	public function show(Club $club, string $encryptedCustomerId): JsonResponse
	{
		$customer = Customer::where('id', session()->get('customer_id', 0))->first();
		if (empty($customer)) {
			try {
				$customer = Customer::where('club_id', $club->id)
					->where('id', Crypt::decrypt($encryptedCustomerId))
					->first();
			} catch (DecryptException $e) {
				$customer = null;
			}
		}

		return response()->json(['customer' => new CustomerResource($customer)]);
	}

	public function forgotPassword(Club $club, CustomerForgotPasswordRequest $request): JsonResponse
	{
		$customer = Customer::where('email', $request->get('email'))
			->where('club_id', $club->id)
			->first();
		$customer->update([
			'widget_channel' => $request->get('widget_channel'),
		]);
		$customer->notify(
			new PasswordRecoveryNotification(
				Country::getCached()
					->where('active', 1)
					->where('code', session()->get('customCountry', null))
					->first() ?? $club->country
			)
		);

		return response()->json([
			'result' => true,
		]);
	}

	public function updatePhoneNumber(UpdateCustomerPhoneRequest $request, Club $club)
	{
		$customer = $club
			->customers()
			->where('email', $request->get('email'))
			->where('club_id', $club->id)
			->whereNull('deleted_at')
			->first();

		if (!empty($customer) && Hash::check($request->get('password'), $customer->password)) {
			$customer->update([
				'phone' => $request->input('phone'),
			]);
		} else {
			return response()->json([
				'status' => false,
				'errors' => [
					'password' => !Hash::check($request->get('password'), $customer->password)
						? ['auth.password']
						: null,
					'email' => empty($customer) ? ['widget.invalid-email'] : null,
				],
			]);
		}

		return response()->json([
			'status' => true,
		]);
	}
}
