<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL\Filter;

use Projom\Storage\Database\Engine\Driver\Language\SQL\Column;
use Projom\Storage\Database\Query\Operator;

class Standard
{
	public static function create(Column $column, Operator $operator, mixed $value, int $filterID): array
	{
		$parameterName = Util::parameterName($column->fields(), $filterID);

		$filter = static::filter($column, $operator, $parameterName);

		$params = [
			$parameterName => $value
		];

		return [
			$filter,
			$params
		];
	}

	public static function filter(Column $column, Operator $operator, string $parameterName): string
	{
		return "$column {$operator->value} :{$parameterName}";
	}
}