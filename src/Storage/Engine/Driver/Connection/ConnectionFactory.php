<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver\Connection;

use Projom\Storage\Engine\Driver\Connection\Config;

class ConnectionFactory
{
	const DEFAULT_PDO_ATTRIBUTES = [
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
	];

	public static function create(): ConnectionFactory
	{
		return new ConnectionFactory();
	}

	public function PDOConnections(array $connectionConfigurations): array
	{
		$PDOConnections = [];
		foreach ($connectionConfigurations as $index => $config) {

			if (!$config->hasDSN())
				$config->dsn = DSN::MySQL($config);

			if (!$config->hasName())
				$config->name = $index + 1;

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
