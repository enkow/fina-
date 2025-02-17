<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTablePreferenceRequest;
use App\Models\TablePreference;
use Illuminate\Http\RedirectResponse;

class TablePreferenceController extends Controller
{
	public function update(UpdateTablePreferenceRequest $request, string $tableName): RedirectResponse
	{
		TablePreference::updateOrCreate(
			[
				'user_id' => auth()->user()->id,
				'name' => TablePreference::getTableFullName(
					$tableName,
					$request->get('tableNamePostfix', null)
				),
			],
			['data' => $request->all()['data']]
		);

		auth()
			->user()
			->flushCache();

		return redirect()->back();
	}
}
