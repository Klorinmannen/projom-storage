<?php

declare(strict_types=1);

namespace JRF\Storage\Internal\Engine;

use JRF\Storage\Internal\Database\SQL\Statement;
use JRF\Storage\Internal\Engine\EngineConfig;
use JRF\Storage\Internal\Engine\EngineType;
use JRF\Storage\Internal\Engine\EngineBase;
use JRF\Storage\Internal\Engine\MySQL;
use JRF\Storage\Internal\Engine\Connection\ConnectionFactory;

class EngineFactory
{
	private ConnectionFactory $connectionFactory;

	public function __construct(ConnectionFactory $connectionFactory)
	{
		$this->connectionFactory = $connectionFactory;
	}

	public static function create(ConnectionFactory $connectionFactory): EngineFactory
	{
		return new EngineFactory($connectionFactory);
	}

	public function createEngine(EngineConfig $config): EngineBase
	{
		if (!$config->hasConnections())
			throw new \Exception('No connections found in engine configuration', 400);

		$engine = match ($config->engineType) {
			EngineType::MySQL => $this->MySQL($config),
			default => throw new \Exception('Engine is not supported', 400)
		};

		return $engine;
	}

	public function MySQL(EngineConfig $config): MySQL
	{
		$connections = $this->connectionFactory->PDOConnections($config->connections);

		// The first connection is the default connection.
		$defaultConnection = array_shift($connections);
		$mysql = MySQL::create($defaultConnection, Statement::create());

		// Add all the other connections.
		foreach ($connections as $connection)
			$mysql->addConnection($connection);

		if ($config->hasLogger())
			$mysql->setLogger($config->logger);

		if ($config->hasOptions())
			$mysql->setOptions($config->options);

		return $mysql;
	}
}
