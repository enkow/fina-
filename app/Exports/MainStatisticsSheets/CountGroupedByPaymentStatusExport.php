<?php

namespace App\Exports\MainStatisticsSheets;

use App\Exports\MainStatisticsSheets\Utilities\GroupedByPaymentStatusBaseUtilities;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class CountGroupedByPaymentStatusExport implements FromArray, WithTitle, ShouldAutoSize
{
	public function __construct(protected array $statistics)
	{
	}

	public function title(): string
	{
		return __('statistics.export-sheets.count-grouped-by-payment-status');
	}

	public function array(): array
	{
		return GroupedByPaymentStatusBaseUtilities::getData($this->statistics, 'count');
	}
}
