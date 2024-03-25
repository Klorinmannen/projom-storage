<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Collection;
use Projom\Storage\Database\QueryInterface;

Interface DriverInterface 
{
	public function execute(string $sql, ?array $params): mixed;
	public function select(Collection $collection, Field $field, array $constraints): mixed;
	public function Query(string $collection): QueryInterface;
	public static function create(array $config): DriverInterface;
}
