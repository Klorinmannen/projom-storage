<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\Drivers;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\Delete;
use Projom\Storage\Database\Query\Insert;
use Projom\Storage\Database\Query\Select;
use Projom\Storage\Database\Query\Update;
use Projom\Storage\Database\SourceInterface;

interface DriverInterface
{
	public static function create(SourceInterface $source): DriverInterface;
	public function type(): Drivers;
	public function select(Select $select): array;
	public function update(Update $update): int;
	public function insert(Insert $insert): int;
	public function delete(Delete $delete): int;
	public function Query(string ...$collections): Query;
	public function execute(string $sql, ?array $params): array;
}
