<?php

namespace App\Exports\MainStatisticsSheets;

use App\Exports\MainStatisticsSheets\Utilities\ChartDataUtilities;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class CountExport implements FromArray, WithTitle, ShouldAutoSize
{
	public function __construct(protected array $statistics)
	{
	}

	public function title(): string
	{
		return __('statistics.export-sheets.count');
	}

	public function array(): array
	{
		return ChartDataUtilities::getData($this->statistics, 'count');
	}
}
