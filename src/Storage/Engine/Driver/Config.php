<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver;

class Config
{
	public array $options;
	public null|string $dsn;
	public null|string $username;
	public null|string $password;
	public null|string $host;
	public null|string|int $port;
	public null|string $database;
	public null|string $charset;
	public null|string $collation;

	public function __construct(array $config)
	{
		$this->options = $config['options'] ?? [];
		$this->dsn = $config['dsn'] ?? null;
		$this->username = $config['username'] ?? null;
		$this->password = $config['password'] ?? null;
		$this->host = $config['host'] ?? null;
		$this->port = $config['port'] ?? null;
		$this->database = $config['database'] ?? null;
		$this->charset = $config['charset'] ?? null;
		$this->collation = $config['collation'] ?? null;
	}
}
