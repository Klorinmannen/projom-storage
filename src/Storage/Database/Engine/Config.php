<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine;

use Projom\Storage\Database\Engine\Driver;

class Config
{
	public readonly Driver|null $driver;
	public readonly string $username;
	public readonly string $password;
	public readonly string $host;
	public readonly string $port;
	public readonly string $database;
	public readonly string $charset;
	public readonly string $collation;
	public readonly string $dsn;
	public readonly array $options;

	public function __construct(array $config)
	{
		$this->driver = Driver::tryFrom($config['driver'] ?? '');
		$this->username = $config['username'] ?? '';
		$this->password = $config['password'] ?? '';
		$this->host = $config['host'] ?? '';
		$this->port = $config['port'] ?? '';
		$this->database = $config['database'] ?? '';
		$this->charset = $config['charset'] ?? '';
		$this->collation = $config['collation'] ?? '';
		$this->dsn = $config['dsn'] ?? '';
		$this->options = $config['options'] ?? [];
	}
}
