<?php

declare(strict_types=1);

namespace JRF\Storage\Internal\Database\SQL\Component\Filter;

use JRF\Storage\Database\Util\Operator;
use JRF\Storage\Internal\Database\SQL\Component\Column;

class Nullable
{
	public static function create(Column $column, Operator $operator): array
	{
		$filter = static::filter($column, $operator);

		return [
			$filter,
			[]
		];
	}

	public static function filter(Column $column, Operator $operator): string
	{
		return "$column {$operator->value}";
	}
}
