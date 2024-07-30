<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL;

use Projom\Storage\Database\Engine\Driver\Language\AccessorInterface;
use Projom\Storage\Database\Engine\Driver\Language\Util;
use Projom\Storage\Database\Query\AggregateFunction;

class Column implements AccessorInterface
{
	private readonly array $fields;
	private readonly string $fieldString;

	public function __construct(array $fields)
	{
		$this->fields = $fields;
		$this->parse($fields);
	}

	public static function create(array $fields): Column
	{
		return new Column($fields);
	}

	public function __toString(): string
	{
		return $this->fieldString;
	}

	public function empty(): bool
	{
		return empty($this->fields);
	}

	public function fields(): array
	{
		return $this->fields;
	}

	private function parse(array $fields): void
	{
		$fields = Util::cleanList($fields);

		$parts = [];
		foreach ($fields as $field)
			$parts[] = $this->createField($field);

		$this->fieldString = Util::join($parts, ', ');
	}

	private function createField(string $field)
	{
		if ($aggregates = $this->matchAggregateFunction($field))
			return $this->buildAggregateFunctionField(...$aggregates);

		if ($this->isFieldValid($field) === false)
			throw new \Exception("Invalid field: $field");

		return Util::splitAndQuoteThenJoin($field, '.');
	}

	private function matchAggregateFunction(string $field): array|null
	{
		$values = AggregateFunction::values();
		$cases = Util::join($values, '|');
		$pattern = "/^({$cases})\(([\w\.\*]+)\)$/i";
		if (!$matches = Util::match($pattern, $field))
			return null;

		$matchedFunction = strtoupper($matches[1] ?? '');
		if (!$aggregateFunction = AggregateFunction::tryFrom($matchedFunction))
			return null;

		if (!$field = $matches[2] ?? '')
			return null;

		return [$aggregateFunction, $field];
	}

	private function buildAggregateFunctionField(AggregateFunction $function, string $field): string|null
	{
		$column = Util::splitAndQuoteThenJoin($field, '.');
		$aggregateFunctionfield = "{$function->value}($column)";
		return $aggregateFunctionfield;
	}

	private function isFieldValid(string $field): bool
	{
		// Allow . as the construction "Table.Field" is viable.
		$pattern = '/([^\w\.\*]+)/i';
		$isValid = Util::match($pattern, $field) ? false : true;
		return $isValid;
	}
}
