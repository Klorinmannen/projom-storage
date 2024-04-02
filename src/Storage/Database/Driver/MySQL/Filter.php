<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Query\AccessorInterface;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\LogicOperators;
use Projom\Storage\Database\Query\Operator;
use Projom\Storage\Database\Query\Operators;
use Projom\Storage\Database\Query\Value;

class Filter implements AccessorInterface
{
	private array $raw = [];
	private array $parsed = [];
	private int $filterID = 0;

	public function __construct(array $filters)
	{
		$this->raw = $filters;
		$this->parsed = array_map(
			fn (array $filter) => $this->parse(...$filter),
			$filters
		);
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
		$params = array_column($this->parsed, 'params');
		return array_merge(...$params) ?: [];
	}

	public function filters(): string
	{
		$operator = LogicOperators::AND->value;
		$filters = array_column($this->parsed, 'filter');
		return implode(" {$operator} ", $filters);
	}

	private function parse(Field $field, Operator $operator, Value $value): array
	{
		$this->filterID++;
		$column = Column::create($field->get());

		switch ($operator->raw()) {
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

	private function nullFilter(Column $column, Operator $operator): array
	{
		return ['filter' => "$column $operator"];
	}

	private function inFilter(Column $column, Operator $operator, Value $value): array
	{
		$parameterName = $this->parameterName($column->joined('_'), $this->filterID);

		$parameters = [];
		$params = [];
		foreach ($value->get() as $id => $val) {
			$parameter = $this->parameterName($parameterName, ++$id);
			$parameters[] = ":$parameter";
			$params[$parameter] = $val;
		}

		$parameterString = implode(',', $parameters);
		$filter = "$column $operator ($parameterString)";
		return ['filter' => $filter, 'params' => $params];
	}

	private function defaultFilter(Column $column, Operator $operator, Value $value): array
	{
		$parameterName = $this->parameterName($column->joined('_'), $this->filterID);
		$filter = "$column $operator :{$parameterName}";
		$params = [
			$parameterName => $value->get()
		];

		return ['filter' => $filter,  'params' => $params];
	}

	private function parameterName(string $column, int $id): string
	{
		$colString = strtolower($column);
		return $colString . '_' . $id;
	}
}