<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Util;

use Projom\Storage\SQL\Util\LogicalOperator;
use Projom\Storage\SQL\Util\Operator;
use Projom\Storage\Util;

class Filter
{
	public static function build(
		string $field,
		mixed $value,
		Operator $operator = Operator::EQ,
		LogicalOperator $logicalOperator = LogicalOperator::AND
	): array {
		return [$field, $operator, $value, $logicalOperator];
	}

	public static function buildGroup(
		array $fieldsWithValues,
		Operator $operator = Operator::EQ,
		LogicalOperator $logicalOperator = LogicalOperator::AND
	): array {
		$filter = [];
		foreach ($fieldsWithValues as $field => $value)
			$filter[] = static::build($field, $value, $operator, $logicalOperator);
		return $filter;
	}

	public static function combine(array ...$filters): array
	{
		return Util::merge($filters);
	}
}
