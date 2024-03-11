<?php

declare(strict_types=1);

namespace Projom\Storage;

use Projom\Storage\Database\Driver;
use Projom\Storage\Database\Engine;
use Projom\Storage\Database\Query;

class Database
{
	use Engine;

	public function __construct(Driver $driver)
	{
		$this->setDriver($driver);
	}

	public static function create(Driver $driver = Driver::MySQL): Database
	{
		return new Database($driver);
	}
	
	// Overrides Engine::query
	public function query(string $table): Query
	{
		return $this->dispatch(table: $table);
	}

	// Overrides Engine::sql
	public function sql(string $query, ?array $params): mixed
	{
		return $this->dispatch(sql: $query, params: $params);
	}
}