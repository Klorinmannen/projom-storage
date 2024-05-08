<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\AccessorInterface;
use Projom\Storage\Database\Driver\MySQL\Util;

class Table implements AccessorInterface
{
	private string $table = '';

	public function __construct(string $table)
	{
		$table = Util::cleanString($table);
		$this->table = Util::quote($table);
	}

	public static function create(string $table): Table
	{
		return new Table($table);
	}

	public function __toString(): string
	{
		return $this->get();
	}

	public function get(): string
	{
		return $this->table;
	}
}
