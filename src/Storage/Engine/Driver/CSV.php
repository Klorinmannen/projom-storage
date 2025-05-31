<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver;

use Projom\Storage\Engine\Driver\Connection\CSVConnection;
use Projom\Storage\Engine\Driver\Connection\ConnectionInterface;
use Projom\Storage\Query\Action;

class CSV extends DriverBase
{
	private CSVConnection $connection;
	private array $connections = [];

	public function __construct(CSVConnection $connection)
	{
		parent::__construct();
		$this->connection = $connection;
		$this->connections[$connection->name()] = $connection;
	}

	public static function create(CSVConnection $connection): CSV
	{
		return new CSV($connection);
	}

	public function dispatch(Action $action, mixed $args): mixed
	{
		return match ($action) {
			default => throw new \Exception("Action: $action is not supported", 400)
		};
	}

	public function addConnection(ConnectionInterface $connection): void
	{
		if (! $connection instanceof CSVConnection)
			throw new \Exception('Connection must be an instance of CSVConnection', 400);
		$this->connections[$connection->name()] = $connection;
	}

	public function changeConnection(int|string $name): void
	{
		if (! array_key_exists($name, $this->connections))
			throw new \Exception("Connection with name $name does not exist", 400);
		$this->connection = $this->connections[$name];
	}
}
