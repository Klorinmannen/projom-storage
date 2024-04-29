<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

use Projom\Storage\Database\LogicalOperators;
use Projom\Storage\Database\Operators;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Value;

class Filter
{
	protected array $fieldsWithValues = [];
	protected array $filters = [];

	public function __construct(
		array $fieldsWithValues,
		Operators $operator,
		LogicalOperators $logicalOperator
	) {
		$this->fieldsWithValues = $fieldsWithValues;
		$this->filters = $this->build($this->fieldsWithValues, $operator, $logicalOperator);
	}

	public static function create(
		array $fieldsWithValues,
		Operators $operator = Operators::EQ,
		LogicalOperators $logicalOperator = LogicalOperators::AND
	): Filter {
		return new Filter($fieldsWithValues, $operator, $logicalOperator);
	}

	protected function build(
		array $fieldsWithValues,
		Operators $operator,
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

	public function merge(Filter ...$others): Filter
	{
		foreach ($others as $filter)
			$this->filters = [...$this->filters, ...$filter->get()];
		return $this;
	}
}
