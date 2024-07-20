<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\Engine\DriverInterface;
use Projom\Storage\Database\Engine\Config;
use Projom\Storage\Database\Engine\Driver;
use Projom\Storage\Database\Engine\DriverFactory;
use Projom\Storage\Database\Engine\SourceFactory;

class Engine
{
	protected static array $drivers = [];
	protected static Driver|null $currentDriver = null;
	protected static DriverFactory|null $driverFactory = null;

	public static function dispatch(
		array|null $collections = null,
		string|null $query = null,
		array|null $params = null
	): object|array {

		$driver = static::driver();

		if ($collections !== null)
			return new Query($driver, $collections);

		if ($query !== null)
			return $driver->execute($query, $params);

		throw new \Exception("Invalid dispatch", 400);
	}

	public static function driver(): DriverInterface
	{
		$driver = static::$drivers[static::$currentDriver?->value] ?? null;
		if ($driver === null)
			throw new \Exception("Engine driver not set", 400);

		return $driver;
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

	public static function setDriver(DriverInterface $engineDriver, Driver $driver): void
	{
		static::$drivers[$driver->value] = $engineDriver;
		static::$currentDriver = $driver;
	}

	public static function start(): void
	{
		$sourceFactory = SourceFactory::create();
		$driverFactory = DriverFactory::create($sourceFactory);
		static::setDriverFactory($driverFactory);
	}

	public static function setDriverFactory(DriverFactory $driverFactory): void
	{
		static::$driverFactory = $driverFactory;
	}

	public static function clear(): void
	{
		static::$drivers = [];
		static::$currentDriver = null;
		static::$driverFactory = null;
	}
}
