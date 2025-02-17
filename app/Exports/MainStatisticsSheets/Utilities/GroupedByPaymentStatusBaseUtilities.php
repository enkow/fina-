<?php

namespace App\Exports\MainStatisticsSheets\Utilities;

class GroupedByPaymentStatusBaseUtilities
{
	public static function getData(array $statistics, string $type): array
	{
		$arrayKeys = array_unique(
			array_keys(
				array_merge(
					$statistics['detailed'][$type]['general'],
					$statistics['detailed'][$type]['canceled']
				)
			)
		);
		$result = [];
		$result[] = [
			ucfirst(__('main.status')),
			__('statistics.reservation-' . $type),
			__('statistics.canceled-reservation-' . $type),
		];
		foreach ($arrayKeys as $arrayKey) {
			$label =
				$statistics['detailed'][$type]['general'][$arrayKey]['label'] ??
				($statistics['detailed'][$type]['canceled'][$arrayKey]['label'] ??
					strtoupper(__('main.sum')));
			$row = [
				$label,
				$statistics['detailed'][$type]['general'][$arrayKey]['value'],
				$statistics['detailed'][$type]['canceled'][$arrayKey]['value'],
			];
			$result[] = MainStatisticsUtilities::prepareBasicResultRow($type, $row);
		}

		return $result;
	}
}
