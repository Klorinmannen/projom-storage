<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Query\AccessorInterface;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\LogicalOperators;
use Projom\Storage\Database\Query\Operator;
use Projom\Storage\Database\Query\Operators;
use Projom\Storage\Database\Query\Value;

class Filter implements AccessorInterface
{
	private array $raw = [];
	private array $filters = [];
	private array $params = [];
	private array $parsed = [];
	private int $filterID = 0;

	public function __construct(array $filters)
	{
		$this->raw = $filters;

		foreach ($filters as $filter) {

			[$field, $operator, $value, $logicalOperator] = $filter;
			[$filter, $params] = $this->parse($field, $operator, $value);

			if (empty($this->filters))
				$this->filters[] = $filter;
			else
				$this->filters[] = "{$logicalOperator->value} $filter";

			if ($params)
				$this->params[] = $params;

			$this->parsed[] = [
				'filter' => $filter,
				'params' => $params
			];
		}
	}

	public static function create(array $filters): Filter
	{
		return new Filter($filters);
	}

	public function __toString(): string
	{
		return $this->filters();
	}

	public function raw(): array
	{
		return $this->raw;
	}

	public function get(): array
	{
		return $this->parsed;
	}

	public function empty(): bool
	{
		return empty($this->parsed);
	}

	public function params(): array
	{
		return array_merge(...$this->params);
	}

	public function filters(): string
	{
		return implode(" ", $this->filters);
	}

	private function parse(
		Field $field,
		Operators $operator,
		Value $value
	): array {

		$this->filterID++;
		$column = Column::create($field->get());

		switch ($operator) {
			case Operators::IS_NULL:
			case Operators::IS_NOT_NULL:
				return $this->nullFilter($column, $operator);

			case Operators::IN:
			case Operators::NOT_IN:
				return $this->inFilter($column, $operator, $value);

			default:
				return $this->defaultFilter($column, $operator, $value);
		}
	}

	private function nullFilter(Column $column, Operators $operator): array
	{
		return [
			"$column {$operator->value}",
			[]
		];
	}

	private function inFilter(Column $column, Operators $operator, Value $value): array
	{
		$parameterName = $this->parameterName($column->joined('_'), $this->filterID);

		$parameters = [];
		$params = [];
		foreach ($value->get() as $id => $val) {
			$parameter = $this->parameterName($parameterName, ++$id);
			$parameters[] = ":$parameter";
			$params[$parameter] = $val;
		}

		$parameterString = implode(', ', $parameters);
		$filter = "$column {$operator->value} ( $parameterString )";

		return [
			$filter,
			$params
		];
	}

	private function defaultFilter(Column $column, Operators $operator, Value $value): array
	{
		$parameterName = $this->parameterName($column->joined('_'), $this->filterID);

		$filter = "$column {$operator->value} :{$parameterName}";
		$params = [
			$parameterName => $value->get()
		];

		return [
			$filter,
			$params
		];
	}

	private function parameterName(string $column, int $id): string
	{
		$colString = strtolower($column);
		return $colString . '_' . $id;
	}
}
