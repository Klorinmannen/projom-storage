<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\SQL;

use Projom\Storage\Database\Language\SQL\AccessorInterface;
use Projom\Storage\Database\Language\SQL\Util\Aggregate;
use Projom\Storage\Database\Language\SQL\Util;

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
		$parts = [];
		foreach ($fields as $field) {

			// If a field does not match it will be ignored.
			if (!$matches = $this->matchField($field))
				continue;

			$parts[] = $this->createField($matches);
		}

		$this->fieldString = Util::join($parts, ', ');
	}
	private function matchField(string $field): array
	{
		$cases = Util::join(Aggregate::values(), '|');
		$pattern = "/^({$cases})?\(?([\w\.\*]+)\)?(\s+as\s+[\w\.]+)?$/i";
		$matches = Util::match($pattern, $field);
		return $matches;
	}

	private function createField(array $matches): null|string
	{
		$function = Util::cleanString($matches[1]);
		$field = Util::cleanString($matches[2]);
		$alias = Util::cleanString($matches[3] ?? '');

		if ($alias)
			$alias = $this->transformAlias($alias);

		if ($function)
			return $this->buildAggregate($function, $field, $alias);

		return $this->buildField($field, $alias);
	}

	private function transformAlias(string $alias): string
	{
		$alias = substr($alias, 2);
		return $alias;
	}

	private function buildAggregate(string $function, string $field, string $alias): null|string
	{
		$function = strtoupper($function);
		if (!$function = Aggregate::tryFrom($function))
			return null;

		$field = Util::splitAndQuoteThenJoin($field, '.');
		$aggregate = $function->buildSQL($field, $alias);

		return $aggregate;
	}

	private function buildField(string $field, string $alias): string
	{
		$field = Util::splitAndQuoteThenJoin($field, '.');
		if ($alias)
			return "$field AS $alias";
		return $field;
	}
}
