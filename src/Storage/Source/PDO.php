<?php

declare(strict_types=1);

namespace Projom\Storage\Source;

use Projom\Storage\Source\DSN;

class PDO
{
	private static \PDO|null $PDO = null;

	const DEFAULT_PDO_OPTIONS = [
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
	];

	public static function get(): \PDO
	{
		return static::$PDO;
	}

	public static function init(array $config, array $options = []): void
	{
		static::$PDO = new \PDO(
			DSN::createString($config),
			$config['username'],
			$config['password'],
			static::DEFAULT_PDO_OPTIONS + $options
		);
	}

	public static function validateConfig(array $config): void
	{
		if (!$server_host = $config['server_host'] ?? '')
			throw new \Exception('Missing server host.', 500);

		if (!$server_port = $config['server_port'] ?? '')
			throw new \Exception('Missing server port.', 500);

		if (!$database_name = $config['database_name'] ?? '')
			throw new \Exception('Missing database name.', 500);

		if (!$username = $config['username'] ?? '')
			throw new \Exception('Missing username.', 500);

		if (!$password = $config['password'] ?? '')
			throw new \Exception('Missing password.', 500);
	}
}
