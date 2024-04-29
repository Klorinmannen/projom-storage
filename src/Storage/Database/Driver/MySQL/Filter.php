<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Operators;
use Projom\Storage\Database\AccessorInterface;
use Projom\Storage\Database\Query\Field;
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
		$this->build();
	}

	public static function create(array $filters): Filter
	{
		return new Filter($filters);
	}

	private function build()
	{
		foreach ($this->raw as $filter) {

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

	private function inFilter(Column $column, Operators $operator, Value $value): array
	{
		$parameterName = $this->parameterName($column->joined('_'), $this->filterID);

		$parameters = [];
		$params = [];
		foreach ($value->get() as $id => $val) {
			$id++;
			$parameter = "{$parameterName}_{$id}";
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
		return "filter_{$colString}_{$id}";
	}
}
