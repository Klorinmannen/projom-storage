<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\AccessorInterface;
use Projom\Storage\Database\Operators;
use Projom\Storage\Database\LogicalOperators;

class Filter implements AccessorInterface
{
	private array $rawFilters = [];
	private array $filters = [];
	private array $params = [];
	private int $filterID = 0;

	public function __construct(array $rawFilters)
	{
		$this->rawFilters = $rawFilters;
	}

	public static function create(
		array $fieldsWithValues,
		Operators $operator = Operators::EQ,
		LogicalOperators $logicalOperator = LogicalOperators::AND
	): Filter {

		$rawFilters = [];
		foreach ($fieldsWithValues as $field => $value) {
			$rawFilters[] = [
				$field,
				$operator,
				$value,
				$logicalOperator
			];
		}

		return new Filter($rawFilters);
	}

	public function __toString(): string
	{
		return $this->filters();
	}

	public function filters(): string
	{
		return implode(" ", $this->filters);
	}

	public function get(): array
	{
		return [
			$this->filters,
			$this->params
		];
	}

	public function empty(): bool
	{
		return empty($this->filters);
	}

	public function params(): array
	{
		return array_merge(...$this->params);
	}

	public function merge(Filter ...$others): Filter
	{
		foreach ($others as $otherFilter)
			$this->rawFilters = [...$this->rawFilters, ...$otherFilter->rawFilters()];
		return $this;
	}

	public function rawFilters(): array
	{
		return $this->rawFilters;
	}

	public function parse(): void
	{
		foreach ($this->rawFilters as $rawFilter) {

			[$field, $operator, $value, $logicalOperator] = $rawFilter;
			[$filter, $params] = $this->build($field, $operator, $value);

			if (empty($this->filters))
				$this->filters[] = $filter;
			else
				$this->filters[] = "{$logicalOperator->value} $filter";

			if ($params)
				$this->params[] = $params;
		}
	}

	private function build(
		string $field,
		Operators $operator,
		mixed $value
	): array {

		$this->filterID++;
		$column = Column::create([$field]);

		switch ($operator) {
			case Operators::IS_NULL:
			case Operators::IS_NOT_NULL:
				return $this->nullFilter($column, $operator);

			case Operators::IN:
			case Operators::NOT_IN:
				return $this->inFilter($column, $operator, $value);

			case Operators::EQ:
			case Operators::NE:
			case Operators::GT:
			case Operators::GTE:
			case Operators::LT:
			case Operators::LTE:
				return $this->defaultFilter($column, $operator, $value);

			default:
				throw new \Exception("Operator not supported: {$operator->value}", 400);
		}
	}

	private function nullFilter(Column $column, Operators $operator): array
	{
		return [
			"$column {$operator->value}",
			[]
		];
	}

	private function inFilter(Column $column, Operators $operator, array $values): array
	{
		$parameterName = $this->parameterName($column->joined('_'), $this->filterID);

		$parameters = [];
		$params = [];
		foreach ($values as $id => $value) {
			$id++;
			$parameter = "{$parameterName}_{$id}";
			$parameters[] = ":$parameter";
			$params[$parameter] = $value;
		}

		$parameterString = implode(', ', $parameters);
		$filter = "$column {$operator->value} ( $parameterString )";

		return [
			$filter,
			$params
		];
	}

	private function defaultFilter(Column $column, Operators $operator, mixed $value): array
	{
		$parameterName = $this->parameterName($column->joined('_'), $this->filterID);

		$filter = "$column {$operator->value} :{$parameterName}";
		$params = [
			$parameterName => $value
		];

		return [
			$filter,
			$params
		];
	}

	private function parameterName(string $column, int $id): string
	{
		$colString = strtolower($column);
		return "filter_{$colString}_{$id}";
	}
}
