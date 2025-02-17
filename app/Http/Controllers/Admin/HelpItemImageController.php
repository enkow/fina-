<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HelpItem;
use App\Models\HelpItemImage;
use App\Models\HelpSection;
use Illuminate\Http\RedirectResponse;

class HelpItemImageController extends Controller
{
	public function store(HelpSection $helpSection, HelpItem $helpItem): RedirectResponse
	{
		$image = request()?->validate([
			'file' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
		])['file'];
		$path = $image->storePublicly('/', ['disk' => 'helpItemImages']);
		$helpItem->helpItemImages()->create([
			'path' => $path,
		]);

		return redirect()
			->back()
			->with('message', [
				'type' => 'success',
				'content' => 'Dodano obrazek',
			]);
	}

	public function destroy(
		HelpSection $helpSection,
		HelpItem $helpItem,
		HelpItemImage $helpItemImage
	): RedirectResponse {
		$helpItemImage->delete();

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => 'Usunięto obrazek',
			]);
	}
}
