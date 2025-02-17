<?php

namespace App\Custom;

class Color
{
	public static array $tailwindBrightnessAdjustments = [
		'50' => 150,
		'75' => 135,
		'100' => 120,
		'200' => 90,
		'300' => 60,
		'350' => 45,
		'400' => 30,
		'500' => 0,
		'600' => -30,
		'700' => -60,
		'800' => -90,
		'900' => -120,
		'950' => -150,
	];

	public static function hexToRgbArray($hex): array
	{
		$hex = str_replace('#', '', $hex);
		if (strlen($hex) === 3) {
			[$r, $g, $b] = sscanf($hex, '%1s%1s%1s');
			$hex = "$r$r$g$g$b$b";
		}
		[$r, $g, $b] = sscanf($hex, '%02x%02x%02x');
		return [$r, $g, $b];
	}

	public static function adjustBrightness($rgb, $adjustment): array
	{
		return array_map(static function ($channel) use ($adjustment) {
			return max(0, min(255, $channel + $adjustment));
		}, $rgb);
	}
	public static function createTailwindPallete($hex): array
	{
		return array_map(static function ($adjustment) use ($hex) {
			return implode(' ', self::adjustBrightness(self::hexToRgbArray($hex), $adjustment));
		}, self::$tailwindBrightnessAdjustments);
	}
}
