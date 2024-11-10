<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Engine\Driver;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine\Driver\Config;
use Projom\Storage\Engine\Driver\DriverFactory;
use Projom\Storage\Engine\Driver\DriverBase;
use Projom\Storage\Engine\Driver\MySQL;
use Projom\Storage\Engine\Driver\Connection\ConnectionFactory;
use Projom\Storage\Engine\Driver\Connection\PDOConnection;

class DriverFactoryTest extends TestCase
{
	#[Test]
	public function createDriver(): void
	{
		$connection = $this->createMock(PDOConnection::class);
		$connection->method('name')->willReturn('name');

		$connectionFactory = $this->createStub(ConnectionFactory::class);
		$connectionFactory->method('PDOConnections')->willReturn([$connection]);

		$driverFactory = DriverFactory::create($connectionFactory);

		$config = new Config([
			'driver' => 'mysql',
			'options' => [],
			'connections' => [
				[
					'name' => 'name',
					'host' => 'localhost',
					'port' => '3306',
					'username' => 'root',
					'password' => 'root',
					'database' => 'nicedbname',
				]
			]
		]);

		$driver = $driverFactory->createDriver($config);
		$this->assertInstanceOf(DriverBase::class, $driver);
		$this->assertInstanceOf(MySQL::class, $driver);
	}

	#[Test]
	public function createDriverExceptionNoConnections(): void
	{
		$connectionFactory = ConnectionFactory::create();
		$driverFactory = DriverFactory::create($connectionFactory);

		$config = new Config([
			'driver' => 'mysql',
			'options' => [],
			'connections' => []
		]);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('No connections found in driver configuration');
		$this->expectExceptionCode(400);
		$driverFactory->createDriver($config);
	}

	#[Test]
	public function createDriverExceptionBadDriverName(): void
	{
		$connectionFactory = ConnectionFactory::create();
		$driverFactory = DriverFactory::create($connectionFactory);

		$config = new Config([
			'driver' => 'bad-driver-name',
			'options' => [],
			'connections' => [
				'nicedbname' => [
					'dsn' => 'sqlite::memory:'
				]
			]
		]);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Driver is not supported');
		$this->expectExceptionCode(400);
		$driverFactory->createDriver($config);
	}
}
