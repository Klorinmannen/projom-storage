<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\Drivers;
use Projom\Storage\Database\QueryInterface;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Collection;
use Projom\Storage\Database\Query\Filter;
use Projom\Storage\Database\SourceInterface;

interface DriverInterface
{
	public function type(): Drivers;
	public function execute(string $sql, ?array $params): mixed;
	public function select(Collection $table, Field $field, Filter $filter): mixed;
	public function Query(string $collection): QueryInterface;
	public static function create(SourceInterface $source): DriverInterface;
}
