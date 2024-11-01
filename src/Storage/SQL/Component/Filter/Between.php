<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Component\Filter;

use Projom\Storage\SQL\Component\Column;
use Projom\Storage\SQL\Util\Operator;

class Between
{
	public static function create(Column $column, Operator $operator, array $values, int $filterID): array
	{
		[$value1, $value2] = $values;

		$parameterName = Util::parameterName($column->fields(), $filterID);

		$parameterName1 = "{$parameterName}_1";
		$parameterName2 = "{$parameterName}_2";

		$filter = static::filter($column, $operator, $parameterName1, $parameterName2);

		$params = [
			$parameterName1 => $value1,
			$parameterName2 => $value2
		];

		return [
			$filter,
			$params
		];
	}

	public static function filter(Column $column, Operator $operator, string $parameterName1, string $parameterName2): string
	{
		return "{$column} {$operator->value} :{$parameterName1} AND :{$parameterName2}";
	}
}