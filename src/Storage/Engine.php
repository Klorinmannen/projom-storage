<?php

declare(strict_types=1);

namespace Projom\Storage;

use Projom\Storage\Query\Action;
use Projom\Storage\Engine\Driver\DriverBase;
use Projom\Storage\Engine\Driver\Config;
use Projom\Storage\Engine\Driver\Driver;
use Projom\Storage\Engine\Driver\DriverFactory;
use Projom\Storage\Engine\Driver\Connection\ConnectionFactory;

class Engine
{
	protected array $drivers = [];
	protected null|Driver $currentDriver = null;
	protected readonly DriverFactory $driverFactory;

	public function __construct(DriverFactory $driverFactory)
	{
		$this->driverFactory = $driverFactory;
	}

	public static function create(array $config = []): Engine
	{
		$connectionFactory = ConnectionFactory::create();
		$driverFactory = DriverFactory::create($connectionFactory);
		$engine = new Engine($driverFactory);

		if ($config)
			$engine->loadDriver($config);		

		return $engine;
	}

	public function clear(): void
	{
		$this->drivers = [];
		$this->currentDriver = null;
	}

	public function dispatch(Action $action, null|Driver $driver = null, mixed $args = null): mixed
	{
		if ($driver !== null)
			if ($this->currentDriver !== $driver)
				$this->useDriver($driver);

		$driver = $this->driver();
		return $driver->dispatch($action, $args);
	}

	public function useDriver(Driver $driver): void
	{
		if (!array_key_exists($driver->value,  $this->drivers))
			throw new \Exception('Driver not loaded', 400);
		$this->currentDriver = $driver;
	}

	public function loadDriver(array $config): Engine
	{
		if ($this->driverFactory === null)
			throw new \Exception('Driver factory not set', 400);

		$config = new Config($config);
		$engineDriver =  $this->driverFactory->createDriver($config);
		$this->setDriver($engineDriver, $config->driver);

		return $this;
	}

	public function setDriver(DriverBase $engineDriver, Driver $driver): void
	{
		$this->drivers[$driver->value] = $engineDriver;
		$this->currentDriver = $driver;
	}

	public function setDriverFactory(DriverFactory $driverFactory): void
	{
		$this->driverFactory = $driverFactory;
	}

	private function driver(): DriverBase
	{
		$driver =  $this->drivers[$this->currentDriver?->value] ?? null;
		if ($driver === null)
			throw new \Exception('Engine driver not set', 400);
		return $driver;
	}
}
