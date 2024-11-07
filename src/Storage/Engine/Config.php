<?php

declare(strict_types=1);

namespace Projom\Storage\Engine;

use Projom\Storage\Engine\Driver;
use Projom\Storage\Engine\Driver\Config as ConnectionConfig;
use Psr\Log\LoggerInterface;

readonly class Config
{
	public null|Driver $driver;
	public array $options;
	public null|LoggerInterface $logger;
	public array $connections;

	public function __construct(array $config)
	{
		$this->driver = Driver::tryFrom($config['driver'] ?? '');
		$this->options = $config['options'] ?? [];
		$this->logger = $config['logger'] ?? null;

		$connections = [];
		foreach ($config['connections'] ?? [] as $name => $connection)
			$connections[$name] = new ConnectionConfig($connection);
		$this->connections = $connections;
	}
}
