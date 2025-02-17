<?php

namespace App\Models;

use App\Notifications\UnpaidInvoiceNotification;

use App\Custom\Timezone;
use Exception;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements HasLocalePreference
{
	use HasApiTokens;
	use HasFactory;
	use HasProfilePhoto;
	use Notifiable;
	use TwoFactorAuthenticatable;
	use SoftDeletes;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var string[]
	 */
	protected $fillable = [
		'type',
		'first_name',
		'last_name',
		'name',
		'email',
		'password',
		'sidebar_reduced',
		'country_id',
		'last_login',
		'created_at',
	];

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret'];

	/**
	 * The attributes that should be cast.
	 *
	 * @var array
	 */
	protected $casts = [
		'last_login' => 'datetime',
		'email_verified_at' => 'datetime',
		'sidebar_reduced' => 'boolean',
	];

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @var array
	 */
	protected $appends = ['profile_photo_url'];

	public function preferredLocale()
	{
		return $this->club->country->locale ?? 'en';
	}

	public static function getUser(int|null $userId): self|null
	{
		return self::where('id', $userId)->first();
	}

	public function flushCache(): void
	{
		Cache::forget('user:' . $this->id . ':data');
		Cache::forget('user:' . $this->id . ':table_preferences');
	}

	public function getData(): self
	{
		return Cache::remember(
			'user:' . auth()->user()?->id . ':data',
			config('cache.model_cache_time'),
			function () {
				return $this->load([
					'club',
                    'club.paymentMethods',
					'club.country',
					'club.tags',
					'club.openingHours',
					'club.openingHoursExceptions',
					'club.games.features',
					'club.products',
				]);
			}
		);
	}

	public function getTablePreferences(): array
	{
		return Cache::remember(
			'user:' . $this->id . ':table_preferences',
			config('cache.model_cache_time'),
			function () {
				$tablePreferences = array_map(static function ($columns) {
					return array_map(static function ($column) {
						return [
							'key' => $column,
							'enabled' => true,
						];
					}, $columns);
				}, config('table-preferences'));

				foreach (Game::all() as $game) {
					// reservation and slot tables have different table preferences
					// depending on game id
					if (!isset($tablePreferences['reservations_' . $game->id])) {
						foreach (
							['reservations_' . $game->id, 'settlement_reservations_' . $game->id]
							as $key
						) {
							$tablePreferences[$key] = $game->getReservationsTablePreference();
						}
						foreach (['app_commission', 'provider_commission'] as $key) {
							$tablePreferences['settlement_reservations_' . $game->id][] = [
								'key' => $key,
								'enabled' => true,
							];
						}
					}
					if (!isset($tablePreferences['slots_' . $game->id])) {
						$tablePreferences['slots_' . $game->id] = $game->getSlotsTablePreference();
					}
				}

				if ($this === null) {
					return $tablePreferences;
				}

				foreach ($this->tablePreferences ?? [] as $tablePreference) {
					$tablePreferences[$tablePreference->name] = $tablePreference->data;
				}

				$tablePreferences['customers'] =
					Club::getClub($this->club_id)?->getCustomersTablePreference() ?? [];

				return $tablePreferences;
			}
		);
	}

	public function club(): BelongsTo
	{
		return $this->belongsTo(Club::class);
	}

	public function country(): BelongsTo
	{
		return $this->belongsTo(Country::class);
	}

	public function tablePreferences(): HasMany
	{
		return $this->hasMany(TablePreference::class);
	}

	public function isType(mixed $roles): bool
	{
		try {
			$roles = is_array($roles) ? $roles : [$roles];
			return in_array($this->type, $roles, true);
		} catch (Exception $e) {
			return false;
		}
	}

	/*
	 * Timezone handle
	 */

	protected function deletedAt(): Attribute
	{
		return Timezone::getClubLocalizedDatetimeAttribute();
	}

	protected function createdAt(): Attribute
	{
		return Timezone::getClubLocalizedDatetimeAttribute();
	}

	protected function updatedAt(): Attribute
	{
		return Timezone::getClubLocalizedDatetimeAttribute();
	}

	protected function lastLogin(): Attribute
	{
		return Timezone::getClubLocalizedDatetimeAttribute();
	}
}
