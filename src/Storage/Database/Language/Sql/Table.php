<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\Sql;

class Table
{
	private string $raw;
	private string $table;

	public function __construct(string $table)
	{
		$this->raw = $table;
		$this->table = $this->format($table);
	}

	public function format(string $table): string
	{
		return "`$table`";
	}

	public function get(): string
	{
		return $this->table;
	}

	public function raw(): string
	{
		return $this->raw;
	}
}