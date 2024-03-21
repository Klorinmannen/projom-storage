<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\QueryInterface;

Interface DriverInterface 
{
	public function execute(string $sql, ?array $params): mixed;
	public function select(string $table, array $constraint): mixed;
	public function Query(string $table): QueryInterface;
	public static function create(array $config): DriverInterface;
}
