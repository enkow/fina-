<?php

namespace App\Rules;

use App\Enums\AnnouncementType;
use App\Models\Announcement;
use Closure;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class AnnouncementDateDoesntExist implements InvokableRule
{
	public AnnouncementType $announcementType;
	public Announcement|null $announcement;

	public function __construct(AnnouncementType $announcementType, Announcement $announcement = null)
	{
		$this->announcement = $announcement;
		$this->announcementType = $announcementType;
	}

	/**
	 * Run the validation rule.
	 *
	 * @param  string                                        $attribute
	 * @param  mixed                                         $value
	 * @param  Closure(string): PotentiallyTranslatedString  $fail
	 *
	 * @return void
	 */
	public function __invoke($attribute, $value, $fail): void
	{
		if (
			club()
				->announcements()
				->when($this->announcement, function ($query) {
					$query->where('id', '!=', $this->announcement->id);
				})
				->where('type', $this->announcementType)
				->where('start_at', $value)
				->when(request()->has('game_id'), function ($q) {
					return $q->where('game_id', request()->get('game_id'));
				})
				->exists()
		) {
			$fail(__('announcement.validation.two-announcement-the-same-day'));
		}
	}
}
