<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL;

use Projom\Storage\Database\Engine\Driver\Language\AccessorInterface;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Column;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Filter\In;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Filter\Nullable;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Filter\Standard;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Filter\Util;
use Projom\Storage\Database\Query\Operator;

class Filter implements AccessorInterface
{
	private readonly string $filter;
	private readonly array $params;

	private int $filterID = 0;

	public function __construct(array $queryFilters)
	{
		$this->parse($queryFilters);
	}

	public static function create(array $queryFilters): Filter
	{
		return new Filter($queryFilters);
	}

	public function __toString(): string
	{
		return $this->filter;
	}

	public function empty(): bool
	{
		return empty($this->filter);
	}

	public function params(): array
	{
		return $this->params;
	}

	private function parse(array $queryFilters): void
	{
		[$filterGroups, $filterParams] = $this->parseQueryFilters($queryFilters);

		$filterGroupParts = $this->parseFilterGroups($filterGroups);
		$filter = Util::join($filterGroupParts, ' ');

		// Remove empty params and set.
		$filterParams = array_filter($filterParams);
		$params = array_merge(...$filterParams);

		$this->filter = $filter;
		$this->params = $params;
	}

	private function parseQueryFilters(array $queryFilters): array
	{
		$filterGroups = [];
		$filterParams = [];
	
		foreach ($queryFilters as $groupIndex => $queryFilter) {

			$hasGroupedFilters = is_array($queryFilter[0] ?? '');
			if ($hasGroupedFilters) {

				foreach ($queryFilter as $groupedQueryFilter) {
					[$filter, $params, $logicalOperator] = $this->buildFilterWithParams($groupedQueryFilter);
					$filterGroups[$groupIndex][] = [$filter, $logicalOperator];
					$filterParams[] = $params;
				}

				continue;
			}

			[$filter, $params, $logicalOperator] = $this->buildFilterWithParams($queryFilter);
			$filterGroups[$groupIndex][] = [$filter, $logicalOperator];
			$filterParams[] = $params;
		}

		return [$filterGroups, $filterParams];
	}

	private function parseFilterGroups(array $filterGroups): array
	{
		$filterGroupParts = [];
		foreach ($filterGroups as $filterGroup) {

			// Flatten filters and logical operators in current order.
			$filterGroup = Util::flatten($filterGroup);

			// Remove the last element, the logical operator and save for later.			
			$logicalOperator = array_pop($filterGroup);
			
			$filterGroup = Util::addParentheses($filterGroup);

			// Merge this filter group with previously added filter groups.
			$filterGroupParts = Util::merge($filterGroupParts, $filterGroup);

			// Push the previously removed logical operator back to the group.
			$filterGroupParts[] = $logicalOperator;
		}

		// Remove the last element, the logical operator, as it is not used.
		array_pop($filterGroupParts);

		// Add parentheses to the whole filter.
		$filterGroupParts = Util::addParentheses($filterGroupParts);

		return $filterGroupParts;
	}

	private function buildFilterWithParams(array $queryFilter): array
	{
		$this->filterID++;

		[$field, $operator, $value, $logicalOperator] = $queryFilter;

		$column = Column::create([$field]);
		[$filter, $params] = $this->createFilterWithParams($column, $operator, $value);

		return [$filter, $params, $logicalOperator->value];
	}

	private function createFilterWithParams(Column $column, Operator $operator, mixed $value): array
	{
		switch ($operator) {
			case Operator::IS_NULL:
			case Operator::IS_NOT_NULL:
				return Nullable::create($column, $operator);

			case Operator::IN:
			case Operator::NOT_IN:
				return In::create($column, $operator, $value, $this->filterID);

			case Operator::EQ:
			case Operator::NE:
			case Operator::GT:
			case Operator::GTE:
			case Operator::LT:
			case Operator::LTE:
			case Operator::LIKE:
			case Operator::NOT_LIKE:
				return Standard::create($column, $operator, $value, $this->filterID);

			default:
				throw new \Exception("Operator not implemented: {$operator->value}", 400);
		}
	}
}
