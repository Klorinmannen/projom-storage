<?php

declare(strict_types=1);

namespace JRF\Storage\Internal\Engine\Driver;

use JRF\Storage\Internal\Database\SQL\Statement;
use JRF\Storage\Internal\Engine\Driver\Config;
use JRF\Storage\Internal\Engine\Driver\Driver;
use JRF\Storage\Internal\Engine\Driver\DriverBase;
use JRF\Storage\Internal\Engine\Driver\MySQL;
use JRF\Storage\Internal\Engine\Driver\Connection\ConnectionFactory;

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
		$connections = $this->connectionFactory->PDOConnections($config->connections);

		// The first connection is the default connection.
		$defaultConnection = array_shift($connections);
		$mysql = MySQL::create($defaultConnection, Statement::create());

		// Add all the other connections.
		foreach ($connections as $connection)
			$mysql->addConnection($connection);

		if ($config->hasLogger())
			$mysql->setLogger($config->logger);

		if ($config->hasOptions())
			$mysql->setOptions($config->options);

		return $mysql;
	}
}
