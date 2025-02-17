<?php

namespace App\Exports\MainStatisticsSheets\Utilities;

class MainStatisticsUtilities
{
	public static function prepareBasicResultRow(string $type, array $row, $multiplier = 0.01): array
	{
		$rowCopy = $row;
		foreach (array_slice($rowCopy, 1) as $key => $value) {
			$row[$key + 1] = match ($type) {
				'count', 'hours' => (string) $value,
				'turnover' => self::formatNumericValue($value, $multiplier),
			};
		}

		return $row;
	}

	public static function formatNumericValue(int|float $amount, float|int $multiplier = 1): string
	{
		return number_format($amount * $multiplier, 2, ',', '');
	}
}
