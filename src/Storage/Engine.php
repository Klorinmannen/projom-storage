<?php

declare(strict_types=1);

namespace Projom\Storage;

use Projom\Storage\Action;
use Projom\Storage\Engine\DriverBase;
use Projom\Storage\Engine\Config;
use Projom\Storage\Engine\Driver;
use Projom\Storage\Engine\DriverFactory;
use Projom\Storage\Engine\Driver\SourceFactory;

class Engine
{
	protected static array $drivers = [];
	protected static null|Driver $currentDriver = null;
	protected static null|DriverFactory $driverFactory = null;

	public static function start(): void
	{
		$sourceFactory = SourceFactory::create();
		$driverFactory = DriverFactory::create($sourceFactory);
		static::setDriverFactory($driverFactory);
	}

	public static function clear(): void
	{
		static::$drivers = [];
		static::$currentDriver = null;
		static::$driverFactory = null;
	}

	public static function dispatch(Action $action, null|Driver $driver = null, mixed $args = null): mixed
	{
		if ($driver !== null)
			static::useDriver($driver);

		$driver = static::driver();
		return $driver->dispatch($action, $args);
	}

	public static function useDriver(Driver $driver): void
	{
		if (!array_key_exists($driver->value, static::$drivers))
			throw new \Exception("Driver not loaded", 400);
		static::$currentDriver = $driver;
	}

	public static function loadDriver(array $config): void
	{
		if (static::$driverFactory === null)
			throw new \Exception("Driver factory not set", 400);

		$config = new Config($config);
		$engineDriver = static::$driverFactory->createDriver($config);
		static::setDriver($engineDriver, $config->driver);
	}

	public static function setDriver(DriverBase $engineDriver, Driver $driver): void
	{
		static::$drivers[$driver->value] = $engineDriver;
		static::$currentDriver = $driver;
	}

	public static function setDriverFactory(DriverFactory $driverFactory): void
	{
		static::$driverFactory = $driverFactory;
	}

	private static function driver(): DriverBase
	{
		$driver = static::$drivers[static::$currentDriver?->value] ?? null;
		if ($driver === null)
			throw new \Exception("Engine driver not set", 400);
		return $driver;
	}
}
