<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver\Connection;

/**
 * Connection configuration.
 */
class Config
{
	public null|int|string $name;
	public readonly array $options;

	public null|string $dsn;
	public readonly null|string $username;
	public readonly null|string $password;
	public readonly null|string $host;
	public readonly null|string|int $port;
	public readonly null|string $database;
	public readonly null|string $charset;
	public readonly null|string $collation;

	public readonly null|string $filePath;

	public function __construct(array $config)
	{
		$this->name = $config['name'] ?? null;
		$this->options = $config['options'] ?? [];

		$this->dsn = $config['dsn'] ?? null;
		$this->username = $config['username'] ?? null;
		$this->password = $config['password'] ?? null;
		$this->host = $config['host'] ?? null;
		$this->port = $config['port'] ?? null;
		$this->database = $config['database'] ?? null;
		$this->charset = $config['charset'] ?? null;
		$this->collation = $config['collation'] ?? null;

		$this->filePath = $config['file_path'] ?? null;
	}

	public function hasDSN(): bool
	{
		return $this->dsn !== null;
	}

	public function hasName(): bool
	{
		return $this->name !== null;
	}

	public function hasFilePath(): bool
	{
		return $this->filePath !== null;
	}
}
