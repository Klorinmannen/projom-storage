<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine;

use Projom\Storage\Database\Engine\Driver;

class Config
{
	public readonly null|Driver $driver;
	public readonly null|string $username;
	public readonly null|string $password;
	public readonly null|string $host;
	public readonly null|string|int $port;
	public readonly null|string $database;
	public readonly null|string $charset;
	public readonly null|string $collation;
	public readonly null|string $dsn;
	public readonly array $options;

	public function __construct(array $config)
	{
		$this->driver = Driver::tryFrom($config['driver'] ?? '');
		$this->username = $config['username'] ?? null;
		$this->password = $config['password'] ?? null;
		$this->host = $config['host'] ?? null;
		$this->port = $config['port'] ?? null;
		$this->database = $config['database'] ?? null;
		$this->charset = $config['charset'] ?? null;
		$this->collation = $config['collation'] ?? null;
		$this->dsn = $config['dsn'] ?? null;
		$this->options = $config['options'] ?? [];
	}
}
