<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver;

use Projom\Storage\Engine\Driver\Driver;
use Projom\Storage\Engine\Driver\Connection\Config as ConnectionConfig;
use Psr\Log\LoggerInterface;

/**
 * Driver configuration.
 */
class Config
{
	public readonly null|Driver $driver;
	public readonly array $options;
	public readonly null|LoggerInterface $logger;
	public array $connections = [];

	public function __construct(array $config)
	{
		$this->driver = Driver::tryFrom($config['driver'] ?? '');
		$this->options = $config['options'] ?? [];
		$this->logger = $config['logger'] ?? null;

		$connections = $config['connections'] ?? [];
		foreach ($connections as $connection)
			$this->connections[] = new ConnectionConfig($connection);
	}

	public function hasLogger(): bool
	{
		return $this->logger !== null;
	}

	public function hasOptions(): bool
	{
		return $this->options ? true : false;
	}

	public function hasConnections(): bool
	{
		return $this->connections ? true : false;
	}
}
