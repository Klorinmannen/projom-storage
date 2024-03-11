<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\PDO\Driver\MySQL;
use Projom\Storage\Database\Query;

enum Driver: string 
{
	case MySQL = 'mysql';
}

trait Engine
{
	private static array $drivers = [];
	private static Driver $currentDriver;

	public static function query(string $table): Query
	{
		return static::dispatch(table: $table);
	}

	public static function sql(string $query, ?array $params): mixed
	{
		return static::dispatch(sql: $query, params: $params);
	}

	protected static function dispatch(string $table = '', string $sql = '', array|null $params = null): mixed
	{
		if (static::driver() === null)
			throw new \Exception("Database driver not set", 400);

		match (true) {
			$table => new Query(static::driver(), $table),
			$sql => static::driver()->execute($sql, $params),
			default => []
		};
	}

	protected static function driver(): DriverInterface|null
	{
		return static::$drivers[static::$currentDriver->value] ?? null;
	}

	public static function setDriver(Driver $currentDriver): void
	{
		if (!in_array($currentDriver, static::$drivers))
			throw new \Exception("Driver {$currentDriver->value} is not loaded", 400);

		static::$currentDriver = $currentDriver;
	}

	public static function loadMySQLDriver(array $config): void
	{
		static::$drivers[Driver::MySQL->value] = new MySQL($config);
		static::$currentDriver = Driver::MySQL;
	}
}
