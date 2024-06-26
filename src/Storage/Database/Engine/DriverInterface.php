<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine;

use Projom\Storage\Database\Query\QueryObject;

interface DriverInterface
{
	public function select(QueryObject $queryObject): array;
	public function update(QueryObject $queryObject): int;
	public function insert(QueryObject $queryObject): int;
	public function delete(QueryObject $queryObject): int;
	public function execute(string $sql, ?array $params): void;
	public function query(string $sql, ?array $params): array|object;
}
