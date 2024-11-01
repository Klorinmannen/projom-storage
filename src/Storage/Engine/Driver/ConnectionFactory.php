<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver;

use Projom\Storage\Engine\Driver\Config;

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

	public function PDOConnection(Config $config): PDOConnection
	{
		$connection = PDOConnection::create(
			$config->dsn,
			$config->username,
			$config->password,
			$config->options
		);
		return $connection;
	}
}
