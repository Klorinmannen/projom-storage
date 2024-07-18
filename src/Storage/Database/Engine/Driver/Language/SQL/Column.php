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

	private function parse(array $fields): void
	{
		$fields = Util::cleanList($fields);

		$parts = [];
		foreach ($fields as $field) {
			if ($aggregates = $this->isAggregateFunction($field))
				$parts[] = $this->buildAggregateFunctionField(...$aggregates);
			else
				$parts[] = $this->buildField($field);
		}

		$this->fieldString = Util::join($parts, ', ');
	}

	public function buildField(string $field): string
	{
		return Util::splitAndQuoteThenJoin($field, '.');
	}

	public function buildAggregateFunctionField(AggregateFunction $function, string $field): string|null
	{
		$column = Util::splitAndQuoteThenJoin($field, '.');
		$aggregateFunctionfield = "{$function->value}($column)";
		return $aggregateFunctionfield;
	}

	public function isAggregateFunction(string $field): array|null
	{
		$pattern = '/(COUNT|AVG|SUM|MIN|MAX)\(([\w\.\*]+)\)/i';
		if (preg_match($pattern, $field, $matches) !== 1)
			return null;

		$matchedFunction = strtoupper($matches[1] ?? '');
		if (!$aggregateFunction = AggregateFunction::tryFrom($matchedFunction))
			return null;

		if (!$field = $matches[2] ?? '')
			return null;

		return [ $aggregateFunction, $field ];
	}

	public function fields(): array
	{
		return $this->fields;
	}
}
