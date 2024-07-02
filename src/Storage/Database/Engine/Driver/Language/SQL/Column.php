<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL;

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
			$parts[] = Util::splitAndQuoteThenJoin($field, '.');

		$this->fieldString = Util::join($parts, ', ');
	}

	public function fields(): array
	{
		return $this->fields;
	}
}
