<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine;

class Config
{
	public string $host;
	public string $port;
	public string $username;
	public string $password;
	public string $database;
	public string $driver;
	public string $charset;
	public string $collation;
	public array $options = [];

	public function __construct(array $config) 
	{
		$this->host = $config['host'] ?? null;
		$this->port = $config['port'] ?? null;
		$this->username = $config['username'] ?? null;
		$this->password = $config['password'] ?? null;
		$this->database = $config['database'] ?? null;
		$this->driver = $config['driver'] ?? null;
		$this->charset = $config['charset'] ?? null;
		$this->collation = $config['collation'] ?? null;
		$this->options = $config['options'] ?? [];
	}
}
