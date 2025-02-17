<?php

namespace App\Exports;

use App\Exports\MainStatisticsSheets\CountExport;
use App\Exports\MainStatisticsSheets\CountGroupedByPaymentStatusExport;
use App\Exports\MainStatisticsSheets\GeneralExport;
use App\Exports\MainStatisticsSheets\HoursExport;
use App\Exports\MainStatisticsSheets\HoursGroupedByPaymentStatusExport;
use App\Exports\MainStatisticsSheets\TurnoverExport;
use App\Exports\MainStatisticsSheets\TurnoverGroupedByPaymentStatusExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MainStatisticsExport implements WithMultipleSheets
{
	public function __construct(protected array $statistics)
	{
	}

	public function sheets(): array
	{
		// Statistics export has a lot of data, so we separate them into many sheets
		return [
			'General Statistics' => new GeneralExport($this->statistics),
			'Turnover Data' => new TurnoverExport($this->statistics),
			'Count Data' => new CountExport($this->statistics),
			'Hours Data' => new HoursExport($this->statistics),
			'Turnover Grouped By Payment' => new TurnoverGroupedByPaymentStatusExport($this->statistics),
			'Count Grouped By Payment' => new CountGroupedByPaymentStatusExport($this->statistics),
			'Hours Grouped By Payment' => new HoursGroupedByPaymentStatusExport($this->statistics),
		];
	}
}
