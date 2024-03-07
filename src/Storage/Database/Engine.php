<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\QueryInterface;

class Engine
{
	private DriverInterface $driver;

	public function __construct(DriverInterface $driver)
	{
		$this->driver = $driver;
	}

	public function query(string $table): QueryInterface
	{
		return $this->driver->query($table);
	}
}
