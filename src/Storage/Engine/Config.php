<?php

declare(strict_types=1);

namespace Projom\Storage\Engine;

use Projom\Storage\Engine\Driver;

readonly class Config
{
	public null|Driver $driver;
	public null|string $username;
	public null|string $password;
	public null|string $host;
	public null|string|int $port;
	public null|string $database;
	public null|string $charset;
	public null|string $collation;
	public null|string $dsn;
	public array $options;
	public array $driverOptions;

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
		$this->driverOptions = $config['driver_options'] ?? [];
	}
}
