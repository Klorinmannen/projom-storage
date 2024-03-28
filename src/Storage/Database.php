<?php

declare(strict_types=1);

namespace Projom\Storage;

use Projom\Storage\Database\Drivers;
use Projom\Storage\Database\Engine;
use Projom\Storage\Database\QueryInterface;

class Database extends Engine 
{
	protected static Drivers $currentDriver;

	private function __construct(Drivers $driver)
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

	public static function create(Drivers $driver = Drivers::MySQL): Database
	{
		return new Database($driver);
	}
}