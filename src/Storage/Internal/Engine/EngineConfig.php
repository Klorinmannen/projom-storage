<?php

declare(strict_types=1);

namespace JRF\Storage\Internal\Engine;

use Psr\Log\LoggerInterface;

use JRF\Storage\Internal\Engine\EngineType;
use JRF\Storage\Internal\Engine\Connection\Config as ConnectionConfig;

/**
 * Driver configuration.
 */
class EngineConfig
{
	public readonly null|EngineType $engine;
	public readonly array $options;
	public readonly null|LoggerInterface $logger;
	public array $connections = [];

	public function __construct(array $config)
	{
		$this->engine = EngineType::tryFrom($config['engine'] ?? '');
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
