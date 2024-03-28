<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Driver\MySQL;

enum Drivers: string 
{
	case MySQL = 'mysql';
}

class Engine
{
	private static array $drivers = [];
	private static Drivers $currentDriver;

	protected static function dispatch(): object|array
	{
		$driver = static::driver();
		if ($driver === null)
			throw new \Exception("Database driver not set", 400);

		return match (func_num_args()) {
			1 => $driver->Query(...func_get_args()),
			2 => $driver->execute(...func_get_args()),
			default => throw new \Exception("Invalid number of arguments", 400)
		};
	}

	private static function driver(): DriverInterface|null
	{
		return static::$drivers[static::$currentDriver] ?? null;
	}

	public static function useDriver(Drivers $newDriver): void
	{
		if (!array_key_exists($newDriver->value, static::$drivers))
			throw new \Exception("Driver {$newDriver->value} is not loaded", 400);

		static::$currentDriver = $newDriver;
	}

	public static function loadDriver(array $config): void
	{
		$driver = $config['driver'] ?? '';
		match ($driver) {
			Drivers::MySQL->value => static::loadMySQLDriver($config),
			default => throw new \Exception("Driver $driver is not supported", 400)
		};
	}

	private static function loadMySQLDriver(array $config): void
	{
		static::$drivers[Drivers::MySQL] = MySQL::create($config);
		static::$currentDriver = Drivers::MySQL;
	}
}
