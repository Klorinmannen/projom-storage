<?php

declare(strict_types=1);

namespace Projom\Storage;

use Projom\Storage\Database\Driver;
use Projom\Storage\Database\Engine;
use Projom\Storage\Database\QueryInterface;

class Database extends Engine 
{
	protected static Driver $currentDriver;

	private function __construct(Driver $driver)
	{
		static::$currentDriver = $driver;
	}

	public function query(string $table): QueryInterface
	{
		return static::dispatch($table);
	}

	public function sql(string $query, ?array $params): mixed
	{
		return static::dispatch($query, $params);
	}

	public static function create(Driver $driver = Driver::MySQL): Database
	{
		return new Database($driver);
	}
}