<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

use Projom\Storage\Database\Query\Operator;
use Projom\Storage\Database\Query\Operators;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Value;
use Projom\Util\Json;

class Filter implements AccessorInterface
{
	protected array $fieldsWithValues = [];
	protected array $filters = [];

	public function __construct(
		Operators $operator,
		array $fieldsWithValues,
		LogicalOperators $logicalOperator = LogicalOperators::AND
	) {
		$this->fieldsWithValues = $fieldsWithValues;
		$this->filters = $this->build($operator, $this->fieldsWithValues, $logicalOperator);
	}

	public static function create(
		Operators $operator,
		array $fieldsWithValues,
		LogicalOperators $logicalOperator = LogicalOperators::AND
	): Filter {
		return new Filter($operator, $fieldsWithValues, $logicalOperator);
	}

	public function __toString(): string
	{
		return Json::encode($this->get());
	}

	protected function build(
		Operators $operator,
		array $fieldsWithValues,
		LogicalOperators $logicalOperator = LogicalOperators::AND
	): array {
		$Filters = [];

		foreach ($fieldsWithValues as $field => $value) {
			$Filters[] = [
				Field::create($field),
				$operator,
				Value::create($value),
				$logicalOperator
			];
		}

		return $Filters;
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
