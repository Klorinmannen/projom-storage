<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL;

use Projom\Storage\Database\Engine\Driver\Language\AccessorInterface;
use Projom\Storage\Database\Engine\Driver\Language\Util;

class Table implements AccessorInterface
{
	private string $table = '';

	public function __construct(array $table)
	{
		$table = Util::cleanList($table);
		$this->table = Util::quoteAndJoin($table, ', ');
	}

	public static function create(array $table): Table
	{
		return new Table($table);
	}

	public function __toString(): string
	{
		return $this->table;
	}

	public function empty(): bool
	{
		return empty($this->table);
	}
}
