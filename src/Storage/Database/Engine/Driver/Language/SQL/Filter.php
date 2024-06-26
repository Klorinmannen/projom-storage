<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL;

use Projom\Storage\Database\Engine\Driver\Language\AccessorInterface;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Column;
use Projom\Storage\Database\Engine\Driver\Language\Util;
use Projom\Storage\Database\Query\Operator;

class Filter implements AccessorInterface
{
	private array $queryFilters = [];
	private array $filters = [];
	private array $params = [];
	private int $filterID = 0;

	public function __construct(array $queryFilters)
	{
		$this->queryFilters = $queryFilters;
		$this->parse($queryFilters);
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
		foreach ($others as $other) {
			$this->queryFilters = array_merge($this->queryFilters, $other->queryFilters());
			$this->parse($other->queryFilters());
		}

		return $this;
	}

	public function queryFilters(): array
	{
		return $this->queryFilters;
	}

	private function parse(array $queryFilters): void
	{
		foreach ($queryFilters as $queryFilter) {

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
		Operator $operator,
		mixed $value
	): array {

		$this->filterID++;
		$column = Column::create([$field]);

		switch ($operator) {
			case Operator::IS_NULL:
			case Operator::IS_NOT_NULL:
				return $this->nullFilter($column, $operator);

			case Operator::IN:
			case Operator::NOT_IN:
				return $this->inFilter($column, $operator, $value);

			case Operator::EQ:
			case Operator::NE:
			case Operator::GT:
			case Operator::GTE:
			case Operator::LT:
			case Operator::LTE:
				return $this->defaultFilter($column, $operator, $value);

			default:
				throw new \Exception("Operator not implemented: {$operator->value}", 400);
		}
	}

	private function nullFilter(Column $column, Operator $operator): array
	{
		return [
			"$column {$operator->value}",
			[]
		];
	}

	private function inFilter(Column $column, Operator $operator, array $values): array
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

	private function defaultFilter(Column $column, Operator $operator, mixed $value): array
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
