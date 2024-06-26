<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine;

use Projom\Storage\Database\Engine\Config;
use Projom\Storage\Database\Engine\Driver;
use Projom\Storage\Database\Engine\DriverInterface;
use Projom\Storage\Database\Engine\Driver\MySQL;
use Projom\Storage\Database\Engine\SourceFactory;

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

	public function createDriver(Config $config): DriverInterface
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
		return MySQL::create($pdo);
	}
}
