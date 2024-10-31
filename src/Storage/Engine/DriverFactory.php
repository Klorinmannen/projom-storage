<?php

declare(strict_types=1);

namespace Projom\Storage\Engine;

use Projom\Storage\Engine\Config;
use Projom\Storage\Engine\Driver;
use Projom\Storage\Engine\DriverBase;
use Projom\Storage\Engine\Driver\Connection\DSN;
use Projom\Storage\Engine\Driver\MySQL;
use Projom\Storage\Engine\Driver\ConnectionFactory;

class DriverFactory
{
	private ConnectionFactory $connectionFactory;

	public function __construct(ConnectionFactory $connectionFactory)
	{
		$this->connectionFactory = $connectionFactory;
	}

	public static function create(ConnectionFactory $connectionFactory): DriverFactory
	{
		return new DriverFactory($connectionFactory);
	}

	public function createDriver(Config $config): DriverBase
	{
		$driver = match ($config->driver) {
			Driver::MySQL => $this->MySQL($config),
			default => throw new \Exception('Driver is not supported', 400)
		};

		return $driver;
	}

	public function MySQL(Config $config): MySQL
	{
		$mysql = MySQL::create();

		if (!$config->connections)
			throw new \Exception('No connections found in driver configuration', 400);

		foreach ($config->connections as $name => $connectionConfig) {
			$connectionConfig->dsn = DSN::MySQL($connectionConfig);
			$pdo = $this->connectionFactory->createPDO($connectionConfig);
			$mysql->addConnection($pdo, $name);
		}

		$name = array_key_first($config->connections);
		$mysql->changeConnection($name);
		$mysql->setOptions($config->options);

		return $mysql;
	}
}
