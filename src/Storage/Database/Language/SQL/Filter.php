<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\SQL;

use Projom\Storage\Database\Language\SQL\AccessorInterface;
use Projom\Storage\Database\Language\SQL\Column;
use Projom\Storage\Database\Language\SQL\Filter\In;
use Projom\Storage\Database\Language\SQL\Filter\Nullable;
use Projom\Storage\Database\Language\SQL\Filter\Standard;
use Projom\Storage\Database\Language\SQL\Filter\Util;
use Projom\Storage\Database\MySQL\Operator;

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

		$filter = Util::join($filterGroups, ' ');

		// Remove empty params and set.
		$filterParams = Util::removeEmpty($filterParams);
		$params = Util::flatten($filterParams);

		$this->filter = $filter;
		$this->params = $params;
	}

	private function parseQueryFilters(array $queryFilters): array
	{
		$filterGroups = [];
		$filterParams = [];

		foreach ($queryFilters as [$queryFilterList, $outerLogicalOperator]) {

			$filterGroup = [];
			foreach ($queryFilterList as [$field, $operator, $value, $innerLogicalOperator]) {

				[$filter, $params] = $this->buildFilterWithParams($field, $operator, $value);

				if ($filterGroup)
					$filterGroup[] = $innerLogicalOperator->value;

				$filterGroup[] = $filter;
				$filterParams[] = $params;
			}

			if ($filterGroups)
				$filterGroups[] = $outerLogicalOperator->value;

			$filterGroups[] = $this->filterGroupToFilterString($filterGroup);
		}

		$filterGroups = Util::addParentheses($filterGroups);

		return [$filterGroups, $filterParams];
	}

	private function filterGroupToFilterString(array $filterGroup): string
	{
		$filterGroupString = Util::join($filterGroup, ' ');
		$filterGroupString = "( $filterGroupString )";
		return $filterGroupString;
	}

	private function buildFilterWithParams(
		string $field,
		Operator $operator,
		mixed $value
	): array {

		$this->filterID++;
		$column = Column::create([$field]);
		[$filter, $params] = $this->createFilterWithParams($column, $operator, $value);

		return [$filter, $params];
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
