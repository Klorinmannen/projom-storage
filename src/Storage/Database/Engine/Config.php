<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine;

use Projom\Storage\Database\Engine\Driver;

class Config
{
	public string $host = '';
	public string $port = '';
	public string $username = '';
	public string $password = '';
	public string $database = '';
	public Driver|null $driver = null;
	public string $charset = '';
	public string $collation = '';
	public array $options = [];

	public function __construct(array $config) 
	{
		$this->host = $config['host'] ?? '';
		$this->port = $config['port'] ?? '';
		$this->username = $config['username'] ?? '';
		$this->password = $config['password'] ?? '';
		$this->database = $config['database'] ?? '';
		$this->driver = Driver::tryFrom($config['driver'] ?? '');
		$this->charset = $config['charset'] ?? '';
		$this->collation = $config['collation'] ?? '';
		$this->options = $config['options'] ?? [];
	}
}
