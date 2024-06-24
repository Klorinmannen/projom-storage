<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\Driver\DriverInterface;
use Projom\Storage\Database\Engine\Config;
use Projom\Storage\Database\Engine\Driver;
use Projom\Storage\Database\Engine\DriverFactory;

class Engine
{
	protected static array $drivers = [];
	protected static Driver|null $currentDriver = null;

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

	public static function useDriver(Driver $driver): void
	{
		if (!array_key_exists($driver->value, static::$drivers))
			throw new \Exception("Driver not loaded", 400);

		static::$currentDriver = $driver;
	}

	public static function setDriver(array $config): void
	{
		$config = new Config($config);
		$driverType = Driver::tryFrom($config->driver);
		$driver = match ($driverType) {
			Driver::MySQL => DriverFactory::MySQL($config),
			default => throw new \Exception("Driver: {$driverType} is not supported", 400)
		};

		static::$drivers[$driverType->value] = $driver;
		static::$currentDriver = $driverType;
	}

	public static function clear(): void
	{
		static::$drivers = [];
		static::$currentDriver = null;
	}
}
