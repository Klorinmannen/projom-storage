<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\SQL;

use Projom\Storage\Database\Driver\AccessorInterface;
use Projom\Storage\Database\Driver\SQL\Util;

class Column implements AccessorInterface
{
	private array $fields = [];
	private string $fieldString = '';

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
		if (!$fields)
			return;

		$this->fieldString = Util::quoteAndJoin($this->fields, ', ');
	}

	public function joined(string $delimeter): string
	{
		return Util::join($this->fields, $delimeter);
	}
}
