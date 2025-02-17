<?php

namespace App\Exports\MainStatisticsSheets\Utilities;

class ChartDataUtilities
{
	public static function getData(array $statistics, string $type): array
	{
		$result = [];
		$result[] = [
			ucfirst(__('main.date')),
			ucfirst(__('statistics.all')),
			__('statistics.online'),
			__('statistics.offline'),
		];
		foreach ($statistics['chart']['online'] as $date => $value) {
			$row = [
				$date,
				$statistics['chart']['online'][$date][$type] + $statistics['chart']['offline'][$date][$type],
				$statistics['chart']['online'][$date][$type],
				$statistics['chart']['offline'][$date][$type],
			];
			$result[] = MainStatisticsUtilities::prepareBasicResultRow($type, $row, 1);
		}

		return $result;
	}
}
