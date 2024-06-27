<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Engine;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\Config;
use Projom\Storage\Database\Engine\DriverFactory;
use Projom\Storage\Database\Engine\DriverInterface;
use Projom\Storage\Database\Engine\MySQLDriver;
use Projom\Storage\Database\Engine\Source\SourceFactory;

class DriverFactoryTest extends TestCase
{
	#[Test]
	public function createDriver(): void
	{
		$pdo = $this->createMock(\PDO::class);

		$sourceFactory = $this->createStub(SourceFactory::class);
		$sourceFactory->method('createPDO')->willReturn($pdo);

		$driverFactory = DriverFactory::create($sourceFactory);

		$config = new Config([
			'driver' => 'mysql',
			'host' => 'localhost',
			'port' => '3306',
			'username' => 'root',
			'password' => 'root'
		]);

		$driver = $driverFactory->createDriver($config);
		$this->assertInstanceOf(DriverInterface::class, $driver);
		$this->assertInstanceOf(MySQLDriver::class, $driver);
	}

	#[Test]
	public function createDriver_exception(): void
	{
		$sourceFactory = SourceFactory::create();
		$driverFactory = DriverFactory::create($sourceFactory);

		$config = new Config([
			'driver' => 'mysqli',
			'host' => 'localhost',
			'port' => '3306',
			'username' => 'root',
			'password' => 'root'
		]);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Driver is not supported');
		$this->expectExceptionCode(400);
		$driverFactory->createDriver($config);
	}
}
