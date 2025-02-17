<?php

namespace App\Models;

use App\Models\Features\BookSingularSlotByCapacity;
use App\Models\Features\FixedReservationDuration;
use App\Models\Features\FullDayReservations;
use App\Models\Features\HasCalendarAnnouncementsSetting;
use App\Models\Features\HasGamePhotoSetting;
use App\Models\Features\HasCustomViews;
use App\Models\Features\HasManagerEmailsSetting;
use App\Models\Features\HasMapSetting;
use App\Models\Features\HasOfflineReservationLimitsSettings;
use App\Models\Features\HasOnlyOnePricelist;
use App\Models\Features\HasPriceAnnouncementsSettings;
use App\Models\Features\HasSlotPeopleLimitSettings;
use App\Models\Features\HasTimers;
use App\Models\Features\HasVisibleCalendarSlotsQuantitySetting;
use App\Models\Features\HasWidgetAnnouncementsSetting;
use App\Models\Features\HasWidgetDurationLimitSetting;
use App\Models\Features\HasWidgetDurationLimitMinimumSetting;
use App\Models\Features\HasWidgetSlotsLimitSetting;
use App\Models\Features\HasWidgetSlotsSelection;
use App\Models\Features\ParentSlotHasOnlineStatus;
use App\Models\Features\PersonAsSlot;
use App\Models\Features\PricePerPerson;
use App\Models\Features\ReservationSlotHasDisplayName;
use App\Models\Features\ReservationSlotHasOccupiedStatus;
use App\Models\Features\SlotHasActiveStatusPerWeekday;
use App\Models\Features\SlotHasBulb;
use App\Models\Features\SlotHasConvenience;
use App\Models\Features\SlotHasLounge;
use App\Models\Features\SlotHasParent;
use App\Models\Features\SlotHasSubtype;
use App\Models\Features\SlotHasType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Parental\HasChildren;
use Sentry\Util\JSON;

class Feature extends BaseModel
{
	use HasFactory, HasChildren, SoftDeletes;

	//Default children variables
	public static array $defaultSlotData = [];
	public array $settings = ['global' => [], 'club' => []];
	public array $defaultTranslations = [];
	public array $conflictedFeatures = [];
	public array $requiredFeatures = [];
	public bool $isTaggableIfGameReservationExist = true;
	public bool $preventReservationProcessing = false;
	public bool $executablePublicly = true;
	protected $fillable = ['game_id', 'type', 'code', 'data'];
	protected $casts = [
		'data' => 'array',
	];
	protected array $childTypes = [
		'book_singular_slot_by_capacity' => BookSingularSlotByCapacity::class,
		'fixed_reservation_duration' => FixedReservationDuration::class,
		'full_day_reservations' => FullDayReservations::class,
		'has_custom_views' => HasCustomViews::class,
		'has_price_announcements_settings' => HasPriceAnnouncementsSettings::class,
		'has_visible_calendar_slots_quantity_setting' => HasVisibleCalendarSlotsQuantitySetting::class,
		'has_only_one_pricelist' => HasOnlyOnePricelist::class,
		'has_widget_duration_limit_minimum_setting' => HasWidgetDurationLimitMinimumSetting::class,
		'has_widget_duration_limit_setting' => HasWidgetDurationLimitSetting::class,
		'has_widget_slots_limit_setting' => HasWidgetSlotsLimitSetting::class,
		'has_widget_slots_selection' => HasWidgetSlotsSelection::class,
		'has_slot_people_limit_settings' => HasSlotPeopleLimitSettings::class,
		'has_widget_announcements_setting' => HasWidgetAnnouncementsSetting::class,
		'has_calendar_announcements_setting' => HasCalendarAnnouncementsSetting::class,
		'has_game_photo_setting' => HasGamePhotoSetting::class,
		'has_timers' => HasTimers::class,
		'person_as_slot' => PersonAsSlot::class,
		'price_per_person' => PricePerPerson::class,
		'has_map_setting' => HasMapSetting::class,
		'slot_has_bulb' => SlotHasBulb::class,
		'slot_has_convenience' => SlotHasConvenience::class,
		'slot_has_lounge' => SlotHasLounge::class,
		'slot_has_type' => SlotHasType::class,
		'slot_has_subtype' => SlotHasSubtype::class,
		'slot_has_parent' => SlotHasParent::class,
		'has_manager_emails_setting' => HasManagerEmailsSetting::class,
		'has_offline_reservation_limits_settings' => HasOfflineReservationLimitsSettings::class,
		'slot_has_active_status_per_weekday' => SlotHasActiveStatusPerWeekday::class,
		'parent_slot_has_online_status' => ParentSlotHasOnlineStatus::class,
		'reservation_slot_has_display_name' => ReservationSlotHasDisplayName::class,
		'reservation_slot_has_occupied_status' => ReservationSlotHasOccupiedStatus::class,
	];

	public function getDynamicTranslations(): array
	{
		return [];
	}

	public static function getCached(): Collection
	{
		return Cache::remember('features', config('cache.model_cache_time'), function () {
			return self::with('game')->get();
		});
	}

	public function settings(): HasMany
	{
		return $this->hasMany(Setting::class);
	}

	public function updateData($data): void
	{
		$this->update(['data' => $data]);
	}

	public function getChildTypes(): array
	{
		return $this->childTypes;
	}

	public function game(): BelongsTo
	{
		return $this->belongsTo(Game::class);
	}

	public function translations(): MorphMany
	{
		return $this->morphMany(Translation::class, 'translatable');
	}

	public function slots(): MorphToMany
	{
		return $this->morphedByMany(Slot::class, 'describable', 'feature_payload');
	}

	public function reservationSlots(): MorphToMany
	{
		return $this->morphedByMany(ReservationSlot::class, 'describable', 'feature_payload')->withPivot(
			'data'
		);
	}

    public function reservations(): MorphToMany
    {
        return $this->morphedByMany(Reservation::class, 'describable', 'feature_payload')->withPivot(
            'data'
        );
    }

	public function insertDefaultData(): void
	{
		$this->update([
			'data' => [],
		]);
	}

	//Default children methods
	public function prepareSlotDataForValidation(array|null $data): array
	{
		return $data ?? [];
	}

	public function getSlotDataValidationNiceNames(): array
	{
		return [];
	}

	public function getSlotDataValidationRules(): array
	{
		return [];
	}

	public function updateSlotData(Slot $slot, array $data = []): bool
	{
		return true;
	}

	public function getReservationDataValidationRules(): array
	{
		return [];
	}

	public function prepareReservationDataForValidation($data = []): array
	{
		return $data ?? [];
	}

	public function getReservationDataValidationNiceNames(): array
	{
		return [];
	}

	public function updateReservationData(ReservationNumber $reservationNumber, array $data): bool
	{
		return true;
	}

	public function prepareDataForAction(array $data): array
	{
		return $data;
	}

	public function prepareDataForSlotSearch(array $data): array
	{
		return $data;
	}

	public function calculateFeatureReservationPrice(
		int &$basePrice,
		int &$finalPrice,
		array $data,
		int $clubId = null
	): void {
	}

	public function slotQueryScope(Builder $query, array $data): Builder
	{
		return $query;
	}

	public function getWidgetData(array $data): array
	{
		return [];
	}
}
