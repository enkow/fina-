<?php

use App\Models\Club;
use Carbon\Carbon;

if (!function_exists('setEnv')) {
	function setEnv($key, $new_value)
	{
		$envFile = app()->environmentFilePath();
		$envFileContent = file_get_contents($envFile);

		$updatedContent = preg_replace('/' . $key . '=.*/', $key . '=' . $new_value, $envFileContent);

		file_put_contents($envFile, $updatedContent);
	}
}

if (!function_exists('club')) {
	function club(): mixed
	{
		return Club::getClub(auth()->user()?->club_id);
	}
}

if (!function_exists('clubId')) {
	function clubId(): mixed
	{
		return auth()->user()?->club_id ?? null;
	}
}

if (!function_exists('amountToSmallestUnits')) {
	function amountToSmallestUnits($amount): int
	{
		return (int) (is_float((float) $amount) && (string) (float) $amount === $amount
			? (float) $amount * 100
			: (float) str_replace(',', '.', $amount) * 100);
	}
}

if (!function_exists('weekDay')) {
	function weekDay(mixed $date = null): int
	{
		if (is_string($date)) {
			$date = now()->parse($date);
		}
		$w = (int) ($date?->format('w') ?? date('w'));

		return $w === 0 ? 7 : $w;
	}
}

if (!function_exists('boolToLocale')) {
	function boolToLocale(int|bool $value): string
	{
		return match ((bool) $value) {
			false => __('main.no'),
			true => __('main.yes'),
		};
	}
}

if (!function_exists('merge_deeply')) {
	function merge_deeply($array1, $array2)
	{
		$merged = $array1;

		foreach ($array2 as $key => $value) {
			if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
				$merged[$key] = merge_deeply($merged[$key], $value);
			} else {
				$merged[$key] = $value;
			}
		}

		return $merged;
	}
}

if (!function_exists('returnResult')) {
	function returnResult(mixed $result, string $type): mixed
	{
		return match ($type) {
			'json' => response()->json($result),
			default => $result,
		};
	}
}

if (!function_exists('appliTimeToCarbon')) {
	function applyTimeToCarbon(Carbon $date, string $time): Carbon
	{
		$timeParts = explode(':', $time);
		return $date
			->hour($timeParts[0])
			->minute($timeParts[1])
			->second($timeParts[2])
			->millisecond(0);
	}
}

if (!function_exists('getMd5FromQuery')) {
	function getMd5FromQuery($query): string
	{
		$sql = $query->toSql();
		foreach ($query->getBindings() as $binding) {
			$value = is_numeric($binding) ? $binding : "'" . $binding . "'";
			$sql = preg_replace('/\?/', $value, $sql, 1);
		}

		$sql = preg_replace('/laravel_reserved_\d+/', 'laravel_reserved', $sql);

		return md5($sql);
	}
}
