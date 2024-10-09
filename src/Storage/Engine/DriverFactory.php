<?php

declare(strict_types=1);

namespace Projom\Storage\Engine;

use Projom\Storage\Engine\Config;
use Projom\Storage\Engine\Driver;
use Projom\Storage\Engine\DriverBase;
use Projom\Storage\Engine\Driver\MySQL;
use Projom\Storage\Engine\Driver\SourceFactory;

class DriverFactory
{
	private SourceFactory $sourceFactory;

	public function __construct(SourceFactory $sourceFactory)
	{
		$this->sourceFactory = $sourceFactory;
	}

	public static function create(SourceFactory $sourceFactory): DriverFactory
	{
		return new DriverFactory($sourceFactory);
	}

	public function createDriver(Config $config): DriverBase
	{
		$driver = match ($config->driver) {
			Driver::MySQL => $this->MySQL($config),
			default => throw new \Exception('Driver is not supported', 400)
		};

		return $driver;
	}

	public function MySQL(Config $config): MySQL
	{
		$pdo = $this->sourceFactory->createPDO($config);
		$mysql = MySQL::create($pdo);
		$mysql->setOptions($config->driverOptions);
		return $mysql;
	}
}
