<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\Drivers;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Collection;
use Projom\Storage\Database\Query\Value;
use Projom\Storage\Database\SourceInterface;

interface DriverInterface
{
	public static function create(SourceInterface $source): DriverInterface;
	public function type(): Drivers;
	public function setFilter(array $fieldsWithValues, Operators $operator, LogicalOperators $logicalOperators): void;
	public function select(Collection $table, Field $field): array;
	public function update(Collection $table, Value $value): int;
	public function insert(Collection $table, Value $value): int;
	public function delete(Collection $table): int;
	public function Query(string $collection): Query;
	public function execute(string $sql, ?array $params): array;
}
