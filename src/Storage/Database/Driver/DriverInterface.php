<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver;

use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\QueryObject;

interface DriverInterface
{
	public function select(QueryObject $queryObject): array;
	public function update(QueryObject $queryObject): int;
	public function insert(QueryObject $queryObject): int;
	public function delete(QueryObject $queryObject): int;
	public function Query(string ...$collections): Query;
	public function execute(string $sql, ?array $params): void;
}
