<?php

declare(strict_types=1);

namespace JRF\Storage;

use JRF\Storage\Internal\Database\Action;
use JRF\Storage\Internal\Engine\Driver\DriverBase;
use JRF\Storage\Internal\Engine\Driver\Config;
use JRF\Storage\Internal\Engine\Driver\Driver;
use JRF\Storage\Internal\Engine\Driver\DriverFactory;
use JRF\Storage\Internal\Engine\Driver\Connection\ConnectionFactory;
use JRF\Storage\Internal\Registry;

class Manager
{
	protected array $drivers = [];
	protected null|Driver $currentDriver = null;

	public static function initialize(array $config): Manager
	{
		$manager = new Manager();
		$connectionFactory = ConnectionFactory::create();
		$driverFactory = DriverFactory::create($connectionFactory);

		foreach ($config as $driverConfig) {
			$config = new Config($driverConfig);
			$engineDriver = $driverFactory->createDriver($config);
			$manager->setDriver($engineDriver, $config->driver);
		}

		Registry::set($manager);

		return $manager;
	}

	private function setDriver(DriverBase $engineDriver, Driver $driver): void
	{
		$this->drivers[$driver->value] = $engineDriver;
		$this->currentDriver = $driver;
	}

	public function dispatch(Action $action, null|Driver $driver = null, mixed $args = null): mixed
	{
		if ($driver !== null)
			$this->useDriver($driver);

		$driver = $this->driver();
		$result = $driver->dispatch($action, $args);

		return $result;
	}

	private function driver(): DriverBase
	{
		$driver =  $this->drivers[$this->currentDriver->value];
		return $driver;
	}

	public function useDriver(Driver $driver): void
	{
		if ($this->currentDriver === $driver)
			return;

		if (!array_key_exists($driver->value,  $this->drivers))
			throw new \Exception('Driver is not initialized.', 400);

		$this->currentDriver = $driver;
	}
}
