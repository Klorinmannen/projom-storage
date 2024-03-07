<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\QueryInterface;

Interface DriverInterface
{
	public function query(string $table): QueryInterface;
	public function execute(string $query, ?array $params): mixed;
	public function select(string $table, string $column, mixed $value, string $operator): mixed;
}
