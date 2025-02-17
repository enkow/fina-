<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/*
 * TablePreference model allow user to change column orders and disable/enable them
 */

class TablePreference extends BaseModel
{
	protected $fillable = ['user_id', 'name', 'data'];

	protected $casts = [
		'data' => 'array',
	];

	public static function getColumns($tableName, $userId = null, $tableNamePostfix = null): array
	{
		$userId = $userId ?? auth()->user()->id;

		return array_column(
			User::find($userId)->getTablePreferences()[
				self::getTableFullName($tableName, $tableNamePostfix)
			] ?? [],
			'key'
		);
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public static function getTableFullName($tableName, $tableNamePostfix = null): string
	{
		return $tableName . ($tableNamePostfix ? '_' . $tableNamePostfix : '');
	}

	public static function getDataArrayFromModel(
		mixed $model,
		array $tablePreference,
		array $extraFields = []
	): array {
		$result = [];
		foreach (array_merge(self::getEnabledColumns($tablePreference), $extraFields) as $column) {
			$result[$column] = $model->{$column} ?? null;
		}

		return $result;
	}

	public static function getEnabledColumns(
		array $tablePreference,
		array $exclude = [],
		array $include = null
	): array {
		$result = array_column(
			array_filter($tablePreference, static function ($a) use ($exclude) {
				return $a['enabled'] && !in_array($a['key'], $exclude, true);
			}),
			'key'
		);

		if ($include) {
			foreach ($include as $key => $column) {
				if (is_array($column)) {
					$keyInResult = array_search($key, $result, true);
					if ($keyInResult !== false) {
						array_splice($result, $keyInResult, 1, $column);
					} else {
						$result = array_merge($result, $column);
					}
				} else {
					$result[] = $column;
				}
			}
		}

		return $result;
	}
}
