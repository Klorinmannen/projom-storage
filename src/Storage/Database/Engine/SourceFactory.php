<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine;

use Projom\Storage\Database\Engine\Config;
use Projom\Storage\Database\Engine\Driver;
use Projom\Storage\Database\Engine\Source\DSN;
use Projom\Storage\Database\Engine\Source\PDO;

class SourceFactory
{
	public static function create(): SourceFactory
	{
		return new SourceFactory();
	}

	public function createPDO(Config $config): \PDO
	{
		$dsn = match ($config->driver) {
			Driver::MySQL => DSN::MySQL($config),
			default => throw new \Exception('Driver is not supported', 400)
		};

		$parsedAttributes = PDO::parseAttributes($config->options['pdo_attributes'] ?? []);

		$pdo = $this->PDO(
			$dsn,
			$config->username,
			$config->password,
			$parsedAttributes
		);

		return $pdo;
	}

	public function PDO(
		string $dsn,
		string $username = null,
		string $password = null,
		array $parsedAttributes = []
	): \PDO {

		$pdo = new \PDO(
			$dsn,
			$username,
			$password,
			$parsedAttributes + PDO::DEFAULT_PDO_ATTRIBUTES
		);

		return $pdo;
	}
}
