<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\AccessorInterface;
use Projom\Storage\Database\Driver\MySQL\Util;

class Column implements AccessorInterface
{
	private array $fields = [];
	private string $fieldString = '';

	public function __construct(array $fields)
	{
		$this->fields = $this->parse($fields);
		$this->fieldString = Util::quoteAndJoin($this->fields, ', ');
	}

	public static function create(array $fields): Column
	{
		return new Column($fields);
	}

	private function parse(array $fields): array
	{
		$fields = Util::cleanList($fields);

		if (!$fields)
			return [];

		if (count($fields) > 1)
			return $fields;

		$fieldString = array_shift($fields);
		return Util::stringToList($fieldString);
	}

	public function __toString(): string
	{
		return $this->get();
	}

	public function get(): string
	{
		return $this->fieldString;
	}

	public function joined(string $delimiter = ','): string
	{
		return Util::join($this->fields, $delimiter);
	}
}
