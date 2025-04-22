<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver\Connection;

use Projom\Storage\Engine\Driver\Connection\Config;

class ConnectionFactory
{
	public static function create(): ConnectionFactory
	{
		return new ConnectionFactory();
	}

	public function PDOConnections(array $connectionConfigurations): array
	{
		$index = 1;
		$PDOConnections = [];
		foreach ($connectionConfigurations as $config) {

			if (!$config->hasDSN())
				$config->dsn = DSN::MySQL($config);

			if (!$config->hasName())
				$config->name = $index++;

			$PDOConnections[] = $this->PDOConnection($config);
		}

		return $PDOConnections;
	}

	public function PDOConnection(Config $config): PDOConnection
	{
		$connection = PDOConnection::create(
			$config->name,
			$config->dsn,
			$config->username,
			$config->password,
			$config->options
		);
		return $connection;
	}
}
