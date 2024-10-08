<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Engine;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine\Config;
use Projom\Storage\Engine\DriverFactory;
use Projom\Storage\Engine\DriverBase;
use Projom\Storage\Engine\Driver\MySQL;
use Projom\Storage\Engine\Driver\SourceFactory;

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
		$this->assertInstanceOf(DriverBase::class, $driver);
		$this->assertInstanceOf(MySQL::class, $driver);
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
