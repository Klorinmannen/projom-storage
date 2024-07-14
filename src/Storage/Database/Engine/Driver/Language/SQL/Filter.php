<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL;

use Projom\Storage\Database\Engine\Driver\Language\AccessorInterface;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Column;
use Projom\Storage\Database\Engine\Driver\Language\Util;
use Projom\Storage\Database\Query\Operator;

class Filter implements AccessorInterface
{
	private readonly string $filters;

	private array $queryFilters = [];
	private array $filterGroups = [];
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
		return $this->filters;
	}

	public function empty(): bool
	{
		return empty($this->filters);
	}

	public function params(): array
	{
		return array_merge(...$this->params);
	}

	public function queryFilters(): array
	{
		return $this->queryFilters;
	}

	private function parse(): void
	{
		foreach ($this->queryFilters as $groupIndex => $queryFilter) {

			$hasGroupedFilters = is_array($queryFilter[0] ?? '');
			if ($hasGroupedFilters) {
				foreach ($queryFilter as $groupedQueryFilter) {
					$this->parseFilter($groupedQueryFilter, $groupIndex);
				}
				continue;
			}

			$this->parseFilter($queryFilter, $groupIndex);
		}

		$filterParts = [];
		foreach ($this->filterGroups as $groupIndex => $filterGroups) {

			// Flatten filters and logical operators in current order.
			$filterGroups = array_merge(...$filterGroups);
			
			// Remove the last element, the logical operator, and push it back after adding parentheses.
			$logicalOperator = array_pop($filterGroups);			
			$filterGroups = $this->addParenthesesToFilter($filterGroups);

			// Merge this filter group with previously added filter groups.
			$filterParts = [...$filterParts, ...$filterGroups];
			
			$filterParts[] = $logicalOperator;
		}

		// Remove the last element, the logical operator as it is not used.
		// And add parentheses.
		array_pop($filterParts);
		$filterParts = $this->addParenthesesToFilter($filterParts);

		$this->filters = Util::join($filterParts, ' ');
	}

	private function addParenthesesToFilter(array $filter): array
	{
		$filterCount = count($filter);

		if ($filterCount > 1) {
			array_unshift($filter, '(');
			$filter[] = ')';
		}

		return $filter;
	}

	private function parseFilter(array $queryFilter, int $groupIndex): void
	{
		[$field, $operator, $value, $logicalOperator] = $queryFilter;

		[$filter, $params] = $this->buildFilterAndParams($field, $operator, $value);

		if ($params)
			$this->params[] = $params;

		$this->filterGroups[$groupIndex][] = [$filter, $logicalOperator->value];
	}

	private function buildFilterAndParams(string $field, Operator $operator, mixed $value): array
	{
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
			case Operator::LIKE:
			case Operator::NOT_LIKE:
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
		$parameterName = $this->parameterName($column->fields(), $this->filterID);

		$parameters = [];
		$params = [];
		foreach ($values as $id => $value) {
			$id++;
			$parameter = "{$parameterName}_{$id}";
			$parameters[] = ":$parameter";
			$params[$parameter] = $value;
		}

		$parameterString = Util::join($parameters, ', ');
		$filter = "$column {$operator->value} ( $parameterString )";

		return [
			$filter,
			$params
		];
	}

	private function defaultFilter(Column $column, Operator $operator, mixed $value): array
	{
		$parameterName = $this->parameterName($column->fields(), $this->filterID);

		$filter = "$column {$operator->value} :{$parameterName}";
		$params = [
			$parameterName => $value
		];

		return [
			$filter,
			$params
		];
	}

	private function parameterName(array $columns, int $id): string
	{
		$colString = Util::join($columns, '_');
		$colString = str_replace(['.', ','], '_', $colString);
		$colString = strtolower($colString);
		return "filter_{$colString}_{$id}";
	}
}
