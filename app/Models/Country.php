<?php

namespace App\Models;

use App\Custom\Fakturownia;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Country extends BaseModel
{
	use Searchable;

	protected $casts = [
		'active' => 'boolean',
	];

	protected $fillable = [
		'active',
		'locale',
		'currency',
		'timezone',
		'payment_method_type',
		'dialing_code',
		'fakturownia_id',
	];

	public static function getCountry($countryId): Country
	{
		return Cache::remember('country:' . $countryId, config('cache.model_cache_time'), function () use (
			$countryId
		) {
			return self::getCached()->find($countryId);
		});
	}

	public static function getByCode(string $code): Country
	{
		return Cache::remember('country:' . $code, config('cache.model_cache_time'), function () use ($code) {
			return self::where('code', $code)->first();
		});
	}

	public static function getCached()
	{
		return Cache::remember('countries', config('cache.model_cache_time'), function () {
			return self::all();
		});
	}

	public static function getByLocale(string $locale): Country|null
	{
		return Cache::remember(
			'country:locale:' . $locale,
			config('cache.model_cache_time'),
			function () use ($locale) {
				return self::getCached()
					->where('locale', $locale)
					->sortByDesc('active')
					->first();
			}
		);
	}

	public function translations(): HasMany
	{
		return $this->hasMany(Translation::class);
	}

	public function clubs(): HasMany
	{
		return $this->hasMany(Club::class);
	}

	public function helpSections(): HasMany
	{
		return $this->hasMany(HelpSection::class)->orderByDesc('weight');
	}

	public function copyHelpSections(Country $from): void
	{
		$copyImageFile = static function (string|null $imagePath, string $directoryName): string|null {
			if ($imagePath === null) {
				return null;
			}

			$itemImageExtension = File::extension(
				storage_path() . '/app/' . $directoryName . '/' . $imagePath
			);

			$newFileName = Str::random(40) . '.' . $itemImageExtension;

			Storage::disk($directoryName)->copy($imagePath, $newFileName);

			return $newFileName;
		};

		foreach ($from->helpSections as $countryFromHelpSection) {
			// Copy help sections
			$copiedHelpSection = $countryFromHelpSection->replicate();
			$copiedHelpSection->country()->associate($this);
			$copiedHelpSection->push();

			// Copy help section items
			foreach ($countryFromHelpSection->helpItems as $helpItem) {
				$copiedHelpSectionItem = $helpItem->replicate();
				$newThumbnailImageFileName = $copyImageFile($helpItem->thumbnail, 'helpItemThumbnails');
				$copiedHelpSectionItem->thumbnail = $newThumbnailImageFileName;
				$copiedHelpSectionItem->helpSection()->associate($copiedHelpSection);
				$copiedHelpSectionItem->push();

				// Copy help section item images
				foreach ($helpItem->helpItemImages as $itemImage) {
					$newImageFileName = $copyImageFile($itemImage->path, 'helpItemImages');
					$copiedItemImage = $itemImage->replicate();
					$copiedItemImage->path = $newImageFileName;
					$copiedItemImage->helpItem()->associate($copiedHelpSectionItem);
					$copiedItemImage->push();
				}
			}
		}
	}

	protected function fakturowniaId(): Attribute
	{
		return Attribute::make(
			get: function (string|null $value) {
				if ($value) {
					return $value;
				}
				$fakturowniaId =
					(new Fakturownia())->createDepartments([
						'name' => __("country.$this->code"),
						'shortcut' => $this->code,
						'use_pattern' => true,
						'invoice_pattern' => "nr-m/mm/yyyy/$this->code",
						'invoice_lang' => $this->locale,
						'tax_no' => '-',
					])['id'] ?? null;
				$this->update([
					'fakturownia_id' => $fakturowniaId,
				]);
				return $fakturowniaId;
			}
		);
	}
}
