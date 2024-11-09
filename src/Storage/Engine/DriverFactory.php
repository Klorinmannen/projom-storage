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
		if (!$config->hasConnections())
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

		$connections = $this->connectionFactory->PDOConnections($config->connections);
		foreach ($connections as $connection)
			$mysql->addConnection($connection);

		$conection = array_shift($connections);
		$mysql->changeConnection($conection->name());

		if ($config->hasLogger())
			$mysql->setLogger($config->logger);

		if ($config->hasOptions())
			$mysql->setOptions($config->options);

		return $mysql;
	}
}
