<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL\Filter;

use Projom\Storage\Database\Engine\Driver\Language\SQL\Column;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Filter\Util;
use Projom\Storage\Database\Query\Operator;

class In
{
	public static function create(Column $column, Operator $operator, array $values, int $filterID): array
	{
		$parameterName = Util::parameterName($column->fields(), $filterID);

		$parameters = [];
		$params = [];
		foreach ($values as $id => $value) {
			$id++;
			$parameter = "{$parameterName}_{$id}";
			$parameters[] = ":$parameter";
			$params[$parameter] = $value;
		}
		
		$parameterString = Util::join($parameters, ', ');

		$filter = static::filter($column, $operator, $parameterString);

		return [
			$filter,
			$params
		];
	}

	public static function filter(Column $column, Operator $operator, string $parameterString): string
	{
		return "$column {$operator->value} ( $parameterString )";
	}
}