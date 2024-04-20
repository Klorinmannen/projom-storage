<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

use Projom\Storage\Database\Query\Operators;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Value;

class Filter implements AccessorInterface
{
	protected array $fieldsWithValues = [];
	protected array $filters = [];

	public function __construct(
		array $fieldsWithValues,
		Operators $operator,
		LogicalOperators $logicalOperator
	) {
		$this->fieldsWithValues = $fieldsWithValues;
		$this->filters = $this->build($operator, $this->fieldsWithValues, $logicalOperator);
	}

	public static function create(
		array $fieldsWithValues,
		Operators $operator = Operators::EQ,
		LogicalOperators $logicalOperator = LogicalOperators::AND
	): Filter {
		return new Filter($fieldsWithValues, $operator, $logicalOperator);
	}

	public function __toString(): string
	{
		return '';
	}

	protected function build(
		Operators $operator,
		array $fieldsWithValues,
		LogicalOperators $logicalOperator = LogicalOperators::AND
	): array {
		$filters = [];

		foreach ($fieldsWithValues as $field => $value) {
			$filters[] = [
				Field::create($field),
				$operator,
				Value::create($value),
				$logicalOperator
			];
		}

		return $filters;
	}

	public function get(): array
	{
		return $this->filters;
	}

	public function raw(): array
	{
		return $this->fieldsWithValues;
	}

	public function merge(Filter ...$others): Filter
	{
		foreach ($others as $filter)
			$this->filters = [...$this->filters, ...$filter->get()];
		return $this;
	}
}
