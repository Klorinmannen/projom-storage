<?php

declare(strict_types=1);

namespace Projom\Storage\Engine;

use Projom\Storage\Engine\Driver;
use Projom\Storage\Engine\Driver\Connection\Config as ConnectionConfig;

readonly class Config
{
	public null|Driver $driver;
	public array $options;
	public array $connections;

	public function __construct(array $config)
	{
		$this->driver = Driver::tryFrom($config['driver'] ?? '');
		$this->options = $config['options'] ?? [];

		$connections = [];
		foreach ($config['connections'] ?? [] as $name => $connection)
			$connections[$name] = new ConnectionConfig($connection);
		$this->connections = $connections;
	}
}
