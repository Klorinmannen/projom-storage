<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

use Projom\Storage\Database\LogicalOperators;
use Projom\Storage\Database\Operators;

/**
 * Base class for all filters.
 */
abstract class Filter
{
	protected array $rawFilters = [];

	/**
	 * Prepares the set filter to be parsed.
	*/
	protected function prepare(
		array $fieldsWithValues,
		Operators $operator,
		LogicalOperators $logicalOperator
	): void {
		foreach ($fieldsWithValues as $field => $value) {
			$this->rawFilters[] = [
				$field,
				$operator,
				$value,
				$logicalOperator
			];
		}
	}

	abstract public static function create(
		array $fieldsWithValues,
		Operators $operator,
		LogicalOperators $logicalOperator
	): Filter;

	abstract public function parse(): void;

	/**
	 * Merges the current class $rawFilter with other filters.
	 */
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
}
