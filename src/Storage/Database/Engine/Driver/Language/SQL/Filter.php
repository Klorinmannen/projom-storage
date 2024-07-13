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
	private string $filters = '';
	private array $filterParts = [];
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

			$hasNestedFilters = is_array($queryFilter[0] ?? '');
			if ($hasNestedFilters) {
				foreach ($queryFilter as $nestedQueryFilter) {
					$this->parseFilter($nestedQueryFilter, $groupIndex);
				}
				continue;
			}

			$this->parseFilter($queryFilter, $groupIndex);
		}

		$filterParts = [];
		foreach ($this->filterParts as $groupIndex => $groupFilters) {

			$groupFilterParts = [];
			foreach ($groupFilters as [$filter, $logicalOperator]) {
				$groupFilterParts[] = $filter;
				$groupFilterParts[] = $logicalOperator;
			}

			$logicalOperator = array_pop($groupFilterParts);
			
			$groupFilterParts = $this->addParentheses($groupFilterParts);
			$filterParts = [...$filterParts, ...$groupFilterParts];
			
			$filterParts[] = $logicalOperator;
		}

		// Remove the last logical operator, it is not used.
		array_pop($filterParts);

		$filterParts = $this->addParentheses($filterParts);

		$this->filters = Util::join($filterParts, ' ');
	}

	private function addParentheses(array $filterParts): array
	{
		$filterPartsCount = count($filterParts);

		if ($filterPartsCount > 1) {
			array_unshift($filterParts, '(');
			$filterParts[] = ')';
		}

		return $filterParts;
	}

	private function parseFilter(array $queryFilter, int $groupIndex): void
	{
		[$field, $operator, $value, $logicalOperator] = $queryFilter;

		[$filter, $params] = $this->buildFilterAndParams($field, $operator, $value);

		if ($params)
			$this->params[] = $params;

		$this->filterParts[$groupIndex][] = [$filter, $logicalOperator->value];
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
