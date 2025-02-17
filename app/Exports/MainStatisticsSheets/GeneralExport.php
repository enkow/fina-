<?php

namespace App\Exports\MainStatisticsSheets;

use App\Exports\MainStatisticsSheets\Utilities\MainStatisticsUtilities;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class GeneralExport implements FromArray, WithTitle, ShouldAutoSize
{
	public function __construct(protected array $statistics)
	{
	}

	public function title(): string
	{
		return __('statistics.export-sheets.general');
	}

	public function array(): array
	{
		$result = [];
		$result[] = [
			__('statistics.singular'),
			__('statistics.all'),
			__('statistics.online'),
			__('statistics.offline'),
		];
		$result[] = [
			__('statistics.turnover'),
			MainStatisticsUtilities::formatNumericValue(
				$this->statistics['reservationsTurnoverSum']['online'] +
					$this->statistics['reservationsTurnoverSum']['offline'],
				0.01
			),
			MainStatisticsUtilities::formatNumericValue(
				$this->statistics['reservationsTurnoverSum']['online'],
				0.01
			),
			MainStatisticsUtilities::formatNumericValue(
				$this->statistics['reservationsTurnoverSum']['offline'],
				0.01
			),
		];
		$result[] = [
			__('statistics.hours'),
			MainStatisticsUtilities::formatNumericValue(
				$this->statistics['reservationsHoursSum']['online'] +
					$this->statistics['reservationsHoursSum']['offline']
			),
			MainStatisticsUtilities::formatNumericValue($this->statistics['reservationsHoursSum']['online']),
			MainStatisticsUtilities::formatNumericValue($this->statistics['reservationsHoursSum']['offline']),
		];
		$result[] = [
			__('statistics.count'),
			MainStatisticsUtilities::formatNumericValue(
				$this->statistics['reservationsCountSum']['online'] +
					$this->statistics['reservationsCountSum']['offline']
			),
			MainStatisticsUtilities::formatNumericValue($this->statistics['reservationsCountSum']['online']),
			MainStatisticsUtilities::formatNumericValue($this->statistics['reservationsCountSum']['offline']),
		];
		$result[] = [
			__('statistics.average-turnover'),
			MainStatisticsUtilities::formatNumericValue(
				$this->statistics['averageReservationsTurnover']['online'] +
					$this->statistics['averageReservationsTurnover']['offline'],
				0.01
			),
			MainStatisticsUtilities::formatNumericValue(
				$this->statistics['averageReservationsTurnover']['online'],
				0.01
			),
			MainStatisticsUtilities::formatNumericValue(
				$this->statistics['averageReservationsTurnover']['offline'],
				0.01
			),
		];
		$result[] = [
			__('statistics.average-hours'),
			MainStatisticsUtilities::formatNumericValue(
				$this->statistics['averageReservationsHours']['online'] +
					$this->statistics['averageReservationsHours']['offline']
			),
			MainStatisticsUtilities::formatNumericValue(
				$this->statistics['averageReservationsHours']['online']
			),
			MainStatisticsUtilities::formatNumericValue(
				$this->statistics['averageReservationsHours']['offline']
			),
		];
		$result[] = [
			__('statistics.average-count'),
			MainStatisticsUtilities::formatNumericValue(
				$this->statistics['averageReservationsCount']['online'] +
					$this->statistics['averageReservationsCount']['offline']
			),
			MainStatisticsUtilities::formatNumericValue(
				$this->statistics['averageReservationsCount']['online']
			),
			MainStatisticsUtilities::formatNumericValue(
				$this->statistics['averageReservationsCount']['offline']
			),
		];
		$result[] = [
			__('statistics.all-customers'),
			MainStatisticsUtilities::formatNumericValue($this->statistics['allCustomersCount']),
			'---',
			'---',
		];
		$result[] = [
			__('statistics.new-customers'),
			MainStatisticsUtilities::formatNumericValue($this->statistics['newCustomersCount']),
			'---',
			'---',
		];
		$result[] = [
			__('statistics.returning-customers'),
			MainStatisticsUtilities::formatNumericValue(
				$this->statistics['allCustomersCount'] - $this->statistics['newCustomersCount']
			),
			'---',
			'---',
		];

		return $result;
	}
}
