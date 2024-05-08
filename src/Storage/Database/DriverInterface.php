<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\Drivers;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\SourceInterface;

interface DriverInterface
{
	public static function create(SourceInterface $source): DriverInterface;
	public function type(): Drivers;
	public function setFields(array $fields): void;
	public function setFilter(array $queryFilters): void;
	public function setSet(array $fieldsWithValues): void;
	public function select(): array;
	public function update(): int;
	public function insert(): int;
	public function delete(): int;
	public function Query(string $collection): Query;
	public function execute(string $sql, ?array $params): array;
}
