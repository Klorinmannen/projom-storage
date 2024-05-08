<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Driver\MySQL;
use Projom\Storage\Database\Source\Factory;

class Engine
{
	protected static array $drivers = [];
	protected static Drivers|null $currentDriver = null;

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

	public static function driver(): DriverInterface|null
	{
		return static::$drivers[static::$currentDriver?->value] ?? null;
	}

	public static function setDriver(DriverInterface $driver): void
	{
		static::$drivers[$driver->type()->value] = $driver;
		static::$currentDriver = $driver->type();
	}

	public static function useDriver(Drivers $driver): void
	{
		if (!array_key_exists($driver->value, static::$drivers))
			throw new \Exception("Driver not loaded", 400);
		
		static::$currentDriver = $driver;
	}

	public static function loadDriver(array $config, array $options = []): void
	{
		$confDriver = $config['driver'] ?? '';
		$driver = Drivers::tryFrom($confDriver);
		match ($driver) {
			Drivers::MySQL => static::loadMySQL($config, $options),
			default => throw new \Exception("Driver {$confDriver} is not supported", 400)
		};
	}

	private static function loadMySQL(array $config, array $options = []): void
	{
		$source = Factory::createPDO($config, $options);
		$driver = MySQL::create($source);
		static::setDriver($driver);
		static::useDriver(Drivers::MySQL);
	}

	public static function clear(): void
	{
		static::$drivers = [];
		static::$currentDriver = null;
	}
}
