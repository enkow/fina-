<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ExportManager
{
	public static function export(
		$headings,
		$headingsTranslations,
		$data,
		$extension,
		$paperData = ['a4', 'portrait']
	) {
		// removing data not present in headings
		$newData = [];
		foreach ($data as $entry) {
			$newDataRow = array_intersect_key($entry, array_flip($headings));
			$sortRowKeys = array_replace(array_flip($headings), $newDataRow);
			$newData[] = array_replace($sortRowKeys, $newDataRow);
		}
		$data = $newData;
		// heading localization prepare
		$headings = array_map(static function ($val) use ($headingsTranslations) {
			return $headingsTranslations[$val] ?? $val;
		}, $headings);
		$fileName = implode('.', ['Bookgame_' . date('YmdHi'), $extension]);

		if ($extension === 'csv') {
			return Excel::download(
				is_array($data) ? new ArrayExport($headings, $data) : new CollectionExport($headings, $data),
				$fileName,
				\Maatwebsite\Excel\Excel::CSV
			);
		}

		if ($extension === 'pdf') {
			return Pdf::setOption(['defaultFont' => 'DejaVu Sans'])
				->loadView('print', ['headings' => $headings, 'data' => $data])
				->setPaper(...$paperData)
				->download($fileName);
		}
	}
}
