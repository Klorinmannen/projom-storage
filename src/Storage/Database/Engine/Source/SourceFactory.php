<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Source;

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
			default => throw new \Exception("Driver: {$config->driver->value} is not supported", 400)
		};

		$parsedAttributes = PDO::parseAttributes($config->options);

		$pdo = new \PDO(
			$dsn,
			$config->username ?? null,
			$config->password ?? null,
			$parsedAttributes + PDO::DEFAULT_PDO_ATTRIBUTES
		);

		return $pdo;
	}
}
