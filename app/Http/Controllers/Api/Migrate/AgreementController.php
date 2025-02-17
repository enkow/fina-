<?php

namespace App\Http\Controllers\Api\Migrate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

use App\Models\ImportedModel;
use App\Models\Club;

class AgreementController extends Controller
{
	public function update(Request $request, int $old_id)
	{
		$assignedClub = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Club())->getMorphClass())
			->first();

		if (!$assignedClub) {
			return response(['status' => 'undefined club'], 400);
		}

		$agreement = $assignedClub->model
			->agreements()
			->where('type', 0)
			->first();

		if ($agreement->file) {
			Storage::disk('clubAgreements')->delete($agreement->file);
		}

		$data = [
			'active' => true,
			'content_type' => 0,
			'required' => 1,
		];

		$rulesUrl = $request->input('terms_url');
		$rulesResponse = Http::get($rulesUrl);

		if ($rulesResponse->successful()) {
			$fileName = uniqid() . '.pdf';
			$data['file'] = $fileName;
			Storage::disk('clubAgreements')->put($fileName, $rulesResponse->body());
		}

		$agreement->update($data);
		$assignedClub->model->flushCache();

		return response(['status' => 'ok', 'action' => 'updated'], 200);
	}

	public function destroy(Request $request, int $old_id)
	{
		$assignedClub = ImportedModel::where('old_id', $old_id)
			->where('model_type', (new Club())->getMorphClass())
			->first();

		if (!$assignedClub) {
			return response(['status' => 'undefined club'], 400);
		}

		$agreement = $assignedClub->model
			->agreements()
			->where('type', 0)
			->first();

		if ($agreement->file) {
			Storage::disk('clubAgreements')->delete($agreement->file);
		}

		$agreement->update([
			'file' => null,
			'text' => null,
			'active' => false,
		]);
		$assignedClub->model->flushCache();

		return response(['status' => 'ok', 'action' => 'deleted'], 200);
	}
}
