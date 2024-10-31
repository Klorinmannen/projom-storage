<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver;

use Projom\Storage\Engine\Driver\Connection\Config;
use Projom\Storage\Engine\Driver\Connection\PDO;

class ConnectionFactory
{
	public static function create(): ConnectionFactory
	{
		return new ConnectionFactory();
	}

	public function createPDO(Config $config): \PDO
	{
		$pdoAttributes = $config->options['pdo_attributes'] ?? [];
		$parsedAttributes = PDO::parseAttributes($pdoAttributes);
		$attributes = $parsedAttributes + PDO::DEFAULT_ATTRIBUTES;

		if ($config->dsn === null)
			throw new \Exception('Connection config is missing dsn', 400);

		$pdo = $this->PDO(
			$config->dsn,
			$config->username,
			$config->password,
			$attributes
		);

		return $pdo;
	}

	public function PDO(
		string $dsn,
		null|string $username = null,
		null|string $password = null,
		array $attributes = []
	): \PDO {

		$pdo = new \PDO(
			$dsn,
			$username,
			$password,
			$attributes
		);

		return $pdo;
	}
}
