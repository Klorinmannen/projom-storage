<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver;

use Projom\Storage\Database\Engine\Config;
use Projom\Storage\Database\Engine\Driver;
use Projom\Storage\Database\Engine\Driver\Source\DSN;
use Projom\Storage\Database\Engine\Driver\Source\PDO;

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
		$attributes = $parsedAttributes + PDO::DEFAULT_ATTRIBUTES;

		$pdo = $this->PDO(
			$dsn,
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
