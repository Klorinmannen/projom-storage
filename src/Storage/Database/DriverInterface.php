<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\Drivers;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Collection;
use Projom\Storage\Database\Query\Filter;
use Projom\Storage\Database\SourceInterface;

interface DriverInterface
{
	public static function create(SourceInterface $source): DriverInterface;
	public function type(): Drivers;
	public function select(Collection $table, Field $field, Filter $filter): mixed;
	public function update(Collection $table, array $fieldsWithValues, Filter $filter): int;
	public function insert(Collection $table, array $fieldsWithValues): int;
	public function delete(Collection $table, Filter $filter): int;
	public function Query(string $collection): Query;
	public function execute(string $sql, ?array $params): mixed;
}
