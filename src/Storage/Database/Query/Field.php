<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

use Projom\Storage\Database\Query\Util;

class Field implements AccessorInterface
{
	private array $raw = '';
	private array $fields = [];

	public function __construct(array $fields)
	{
		$this->raw = Util::cleanList($fields);
		$this->fields = $this->build($this->raw);
	}

	private function build(array $fields): array
	{
		if (!$fields)
			return [];

		if (count($fields) > 1)
			return $fields;

		$fieldString = array_shift($fields);
		return Util::stringToList($fieldString);
	}

	public function get(): string
	{
		return Util::quoteAndJoin($this->fields);
	}

	public function raw(): array
	{
		return $this->raw;
	}

	public function quoted(): array
	{
		return Util::quoteList($this->fields);
	}

	public function joined(string $delimeter = ','): string
	{
		return Util::join($this->fields, $delimeter);
	}

	public static function create(string ...$fields): Field
	{
		return new Field($fields);
	}
}