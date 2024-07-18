<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL\Filter;

use Projom\Storage\Database\Engine\Driver\Language\SQL\Column;
use Projom\Storage\Database\Query\Operator;

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