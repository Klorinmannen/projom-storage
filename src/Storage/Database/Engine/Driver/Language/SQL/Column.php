<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL;

use Projom\Storage\Database\Query\AggregateFunction;
use Projom\Storage\Database\Engine\Driver\Language\AccessorInterface;
use Projom\Storage\Database\Engine\Driver\Language\Util;

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
		foreach ($fields as $field)
			$parts[] = $this->parseField($field);

		$this->fieldString = Util::join($parts, ', ');
	}

	public function parseField(string $field): string
	{
		if ($aggregateField = $this->parseAggregateFunction($field))
			return $aggregateField;

		return Util::splitAndQuoteThenJoin($field, '.');
	}

	public function parseAggregateFunction(string $field): string|null
	{
		$pattern = '/(COUNT|AVG|SUM|MIN|MAX)\(([\w\.\*]+)\)/i';
		if (preg_match($pattern, $field, $matches) !== 1)
			return null;

		$function = strtoupper($matches[1] ?? '');
		if (!$this->matchAggregateFunction($function))
			return null;

		if (!$column = $matches[2] ?? '')
			return null;

		$aggregateFunction = AggregateFunction::tryFrom($function);

		$column = Util::splitAndQuoteThenJoin($column, '.');
		$field = "{$aggregateFunction->value}($column)";

		return $field;
	}

	public function matchAggregateFunction(string $function): bool 
	{
		$aggregateFunction = AggregateFunction::tryFrom($function);
		return $aggregateFunction !== null;
	}

	public function fields(): array
	{
		return $this->fields;
	}
}
