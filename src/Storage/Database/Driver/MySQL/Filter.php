<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Driver\AccessorInterface;
use Projom\Storage\Database\Operators;

class Filter implements AccessorInterface
{
	private array $queryFilters = [];
	private array $filters = [];
	private array $params = [];
	private int $filterID = 0;

	public function __construct(array $queryFilters)
	{
		$this->queryFilters = $queryFilters;
	}

	public static function create(array $queryFilters): Filter
	{
		return new Filter($queryFilters);
	}

	public function __toString(): string
	{
		return Util::join($this->filters, " ");
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
			$this->queryFilters = [$this->queryFilters, $otherFilter->queryFilters()];
		return $this;
	}

	public function queryFilters(): array
	{
		return $this->queryFilters;
	}

	public function parse(): void
	{
		foreach ($this->queryFilters as $queryFilter) {

			[$field, $operator, $value, $logicalOperator] = $queryFilter;
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
