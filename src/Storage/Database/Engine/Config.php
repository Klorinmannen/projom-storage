<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine;

use Projom\Storage\Database\Engine\Driver;

class Config
{
	public readonly Driver|null $driver;
	public readonly string|null $username;
	public readonly string|null $password;
	public readonly string|null $host;
	public readonly string|int|null $port;
	public readonly string|null $database;
	public readonly string|null $charset;
	public readonly string|null $collation;
	public readonly string|null $dsn;
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
