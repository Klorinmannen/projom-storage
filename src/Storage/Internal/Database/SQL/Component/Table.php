<?php

declare(strict_types=1);

namespace JRF\Storage\Internal\Database\SQL\Component;

use JRF\Storage\Internal\Database\SQL\Component\ComponentInterface;
use JRF\Storage\Internal\Database\SQL\Util;

class Table implements ComponentInterface
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
