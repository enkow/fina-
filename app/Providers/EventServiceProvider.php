<?php

namespace App\Providers;

use App\Listeners\LogSuccessfulLogin;
use App\Models\Agreement;
use App\Models\Announcement;
use App\Models\Club;
use App\Models\Country;
use App\Models\DiscountCode;
use App\Models\Feature;
use App\Models\Game;
use App\Models\HelpItem;
use App\Models\HelpItemImage;
use App\Models\HelpSection;
use App\Models\ManagerEmail;
use App\Models\OpeningHours;
use App\Models\OpeningHoursException;
use App\Models\PaymentMethod;
use App\Models\Pricelist;
use App\Models\PricelistException;
use App\Models\PricelistItem;
use App\Models\Refund;
use App\Models\Reservation;
use App\Models\ReservationSlot;
use App\Models\ReservationType;
use App\Models\Set;
use App\Models\Setting;
use App\Models\Slot;
use App\Models\SpecialOffer;
use App\Models\User;
use App\Observers\AgreementObserver;
use App\Observers\AnnouncementObserver;
use App\Observers\ClubObserver;
use App\Observers\CountryObserver;
use App\Observers\CustomerObserver;
use App\Observers\DiscountCodeObserver;
use App\Observers\FeatureObserver;
use App\Observers\GameObserver;
use App\Observers\HelpItemImageObserver;
use App\Observers\HelpItemObserver;
use App\Observers\HelpSectionObserver;
use App\Observers\ManagerEmailObserver;
use App\Observers\OpeningHoursExceptionObserver;
use App\Observers\OpeningHoursObserver;
use App\Observers\PaymentMethodObserver;
use App\Observers\PricelistExceptionObserver;
use App\Observers\PricelistItemObserver;
use App\Observers\PricelistObserver;
use App\Observers\RefundObserver;
use App\Observers\ReservationObserver;
use App\Observers\ReservationSlotObserver;
use App\Observers\ReservationTypeObserver;
use App\Observers\SetObserver;
use App\Observers\SettingObserver;
use App\Observers\SlotObserver;
use App\Observers\SpecialOfferObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
	/**
	 * The event to listener mappings for the application.
	 *
	 * @var array<class-string, array<int, class-string>>
	 */
	protected $listen = [
		Registered::class => [SendEmailVerificationNotification::class],
		Login::class => [LogSuccessfulLogin::class],
	];

	protected $observers = [
		Announcement::class => [AnnouncementObserver::class],
		Agreement::class => [AgreementObserver::class],
		Club::class => [ClubObserver::class],
		DiscountCode::class => [DiscountCodeObserver::class],
		Game::class => [GameObserver::class],
		Feature::class => [FeatureObserver::class],
		HelpItem::class => [HelpItemObserver::class],
		HelpItemImage::class => [HelpItemImageObserver::class],
		HelpSection::class => [HelpSectionObserver::class],
		ManagerEmail::class => [ManagerEmailObserver::class],
		OpeningHours::class => [OpeningHoursObserver::class],
		OpeningHoursException::class => [OpeningHoursExceptionObserver::class],
		PaymentMethod::class => [PaymentMethodObserver::class],
		Pricelist::class => [PricelistObserver::class],
		PricelistItem::class => [PricelistItemObserver::class],
		PricelistException::class => [PricelistExceptionObserver::class],
		Refund::class => [RefundObserver::class],
		Reservation::class => [ReservationObserver::class],
		ReservationSlot::class => [ReservationSlotObserver::class],
		ReservationType::class => [ReservationTypeObserver::class],
		Set::class => [SetObserver::class],
		Setting::class => [SettingObserver::class],
		Slot::class => [SlotObserver::class],
		SpecialOffer::class => [SpecialOfferObserver::class],
		User::class => [UserObserver::class],
		Country::class => [CountryObserver::class],
	];

	/**
	 * Register any events for your application.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Determine if events and listeners should be automatically discovered.
	 *
	 * @return bool
	 */
	public function shouldDiscoverEvents()
	{
		return false;
	}
}
