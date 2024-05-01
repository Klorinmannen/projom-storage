<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\AccessorInterface;
use Projom\Storage\Database\Driver\MySQL\Util;
use Projom\Storage\Database\Query\Field;

class Column extends Field implements AccessorInterface
{
	private string $fieldString = '';

	public function __construct(array $fields)
	{
		$this->fields = $this->prepare($fields);
	}

	public static function create(array $fields): Column
	{
		return new Column($fields);
	}

	public function parse(): void 
	{
		$this->fieldString = Util::quoteAndJoin($this->fields, ', ');
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
