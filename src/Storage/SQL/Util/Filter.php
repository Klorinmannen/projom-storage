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

	public static function list(
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

	public static function eq(string $field, mixed $value): array
	{
		return static::build($field, $value, Operator::EQ);
	}

	public static function ne(string $field, mixed $value): array
	{
		return static::build($field, $value, Operator::NE);
	}

	public static function gt(string $field, mixed $value): array
	{
		return static::build($field, $value, Operator::GT);
	}

	public static function ge(string $field, mixed $value): array
	{
		return static::build($field, $value, Operator::GTE);
	}

	public static function lt(string $field, mixed $value): array
	{
		return static::build($field, $value, Operator::LT);
	}

	public static function le(string $field, mixed $value): array
	{
		return static::build($field, $value, Operator::LTE);
	}

	public static function like(string $field, mixed $value): array
	{
		return static::build($field, $value, Operator::LIKE);
	}

	public static function notLike(string $field, mixed $value): array
	{
		return static::build($field, $value, Operator::NOT_LIKE);
	}

	public static function in(string $field, array $values): array
	{
		return static::build($field, $values, Operator::IN);
	}

	public static function notIn(string $field, array $values): array
	{
		return static::build($field, $values, Operator::NOT_IN);
	}

	public static function isNull(string $field): array
	{
		return static::build($field, null, Operator::IS_NULL);
	}

	public static function isNotNull(string $field): array
	{
		return static::build($field, null, Operator::IS_NOT_NULL);
	}
}
