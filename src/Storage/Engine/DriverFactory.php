<?php

declare(strict_types=1);

namespace Projom\Storage\Engine;

use Projom\Storage\Engine\Config;
use Projom\Storage\Engine\Driver;
use Projom\Storage\Engine\DriverBase;
use Projom\Storage\Engine\Driver\DSN;
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
		if (!$config->connections)
			throw new \Exception('No connections found in driver configuration', 400);

		$driver = match ($config->driver) {
			Driver::MySQL => $this->MySQL($config),
			default => throw new \Exception('Driver is not supported', 400)
		};

		return $driver;
	}

	public function MySQL(Config $config): MySQL
	{
		$mysql = MySQL::create();
		foreach ($config->connections as $name => $connectionConfig) {
			if ($connectionConfig->dsn === null)
				$connectionConfig->dsn = DSN::MySQL($connectionConfig);
			$connection = $this->connectionFactory->PDOConnection($connectionConfig);
			$mysql->setConnection($connection, $name);
		}

		$name = array_key_first($config->connections);
		$mysql->changeConnection($name);
		$mysql->setOptions($config->options);

		return $mysql;
	}
}
