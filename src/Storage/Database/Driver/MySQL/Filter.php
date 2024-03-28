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

	public function params(): array
    {
        $params = array_column($this->parsed, 'params');
        return array_merge(...$params) ?: null;
    }

    public function filters(): string
    {        
        $operator = LogicOperators::AND;
        $filters = array_column($this->parsed, 'filter');
        return implode(" {$operator} ", $filters);
    }

	private function parse(Field $field, Operator $operator, Value $value): array
	{
		$column = Column::create($field->get());

		switch ($operator) {
			case Operators::IS_NULL:
			case Operators::IS_NOT_NULL:
				return $this->nullFilter($column, $operator);

			case Operators::IN:
			case Operators::NOT_IN:
				return [];

			case Operators::LIKE:
			case Operators::NOT_LIKE:
				return [];

			default:
				return $this->normFilter($column, $operator, $value);
		}
	}

	private function normFilter(Column $column, Operator $operator, Value $value): array
	{
		$parameterName = $this->parameterName($column, $operator, $value);
		$filter = "$column $operator :$parameterName";
		$params = [
			$parameterName => $value->get()
		];

		return ['filter' => $filter,  'params' => $params];
	}

	private function nullFilter(Column $column, Operator $operator): array
	{
		return ['filter' => "$column $operator"];
	}

	private function parameterName(Column $column, Operator $operator, Value $value): string
	{
		$fieldString = strtolower($column->joined('_'));
		$md5_short = substr(md5($this->seed($column, $operator, $value)), -10);
		return 'named_' . $fieldString . '_' . $md5_short;
	}

	private function seed(Column $column, Operator $operator, Value $value): string
	{
		return $column . $operator . $value;
	}
}
