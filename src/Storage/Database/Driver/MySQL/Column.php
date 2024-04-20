<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Query\AccessorInterface;
use Projom\Storage\Database\Driver\MySQL\Util;

class Column implements AccessorInterface
{
	private array $raw = [];
	private string $fields = '';

	public function __construct(array $fields)
	{
		$this->raw = Util::cleanList($fields);
		$this->fields = Util::quoteAndJoin($this->raw, ', ');
	}

	public static function create(array $fields): Column
	{
		return new Column($fields);
	}

	public function __toString(): string
	{
		return $this->get();
	}

	public function raw(): array
	{
		return $this->raw;
	}

	public function get(): string
	{
		return $this->fields;
	}

	public function joined(string $delimiter = ','): string
	{
		return Util::join($this->raw, $delimiter);
	}
}
