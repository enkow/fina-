<?php

namespace App\Http\Controllers\Club;

use App\Enums\AnnouncementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Club\AccessAnnouncementRequest;
use App\Http\Requests\Club\StoreAnnouncementRequest;
use App\Http\Requests\Club\UpdateAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class AnnouncementController extends Controller
{
	public function index(): Response
	{
		$announcements = AnnouncementResource::collection(
			club()
				->announcements()
				->where('type', AnnouncementType::Panel)
				->orderBy('start_at', 'ASC')
				->paginate(request()['perPage']['announcements'] ?? 10)
		);

		return Inertia::render('Club/Announcements/Index', compact(['announcements']));
	}

	public function store(StoreAnnouncementRequest $request): RedirectResponse
	{
		$newStart = Carbon::parse($request->input('start_at'));
		$newStop = Carbon::parse($request->input('end_at'));
		$currentDate = Carbon::now();

		$announcementsInFuture = club()
			->announcements()
			->where('type', $request->input('type'))
			->where('game_id', $request->input('game_id'))
			->where(function ($query) use ($currentDate) {
				$query->where('start_at', '>', $currentDate)->orWhere('end_at', '>', $currentDate);
			})
			->get();

		foreach ($announcementsInFuture as $announcement) {
			$existStart = Carbon::create($announcement->start_at);
			$existStop = Carbon::create($announcement->end_at);

			if (
				$newStart->between($existStart, $existStop) ||
				$newStop->between($existStart, $existStop) ||
				$existStart->between($newStart, $newStop) ||
				$existStop->between($newStart, $newStop)
			) {
				return redirect()
					->back()
					->with('message', [
						'type' => 'error',
						'content' => __('announcement.validation.two-announcement-the-same-day'),
					]);
			}
		}

		club()
			->announcements()
			->create(
				$request->only([
					'game_id',
					'type',
					'start_at',
					'end_at',
					'content',
					'content_top',
					'content_bottom',
				])
			);

		return redirect()
			->back()
			->with('message', [
				'type' => 'success',
				'content' => __('announcement.successfully-stored'),
			]);
	}

	public function edit(AccessAnnouncementRequest $request, Announcement $announcement): Response
	{
		return Inertia::render('Club/Announcements/Edit', [
			'announcement' => new AnnouncementResource($announcement),
		]);
	}

	public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
	{
		$announcement->update($request->only(['start_at', 'end_at', 'content']));

		return redirect()
			->route('club.announcements.index')
			->with('message', [
				'type' => 'info',
				'content' => __('announcement.successfully-updated'),
			]);
	}

	public function destroy(AccessAnnouncementRequest $request, Announcement $announcement): RedirectResponse
	{
		$announcement->delete();

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => __('announcement.successfully-destroyed'),
			]);
	}
}
