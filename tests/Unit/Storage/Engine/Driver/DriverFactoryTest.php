<?php

declare(strict_types=1);

namespace JRF\Tests\Unit\Storage\Engine\Driver;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use JRF\Storage\Internal\Engine\Driver\EngineConfig;
use JRF\Storage\Internal\Engine\Driver\EngineFactory;
use JRF\Storage\Internal\Engine\Driver\EngineBase;
use JRF\Storage\Internal\Engine\Driver\MySQL;
use JRF\Storage\Internal\Engine\Driver\Connection\ConnectionFactory;
use JRF\Storage\Engine\Driver\Connection\PDOConnection;

class DriverFactoryTest extends TestCase
{
	#[Test]
	public function createDriver(): void
	{
		$connection = $this->createMock(PDOConnection::class);
		$connection->method('name')->willReturn('name');

		$connectionFactory = $this->createStub(ConnectionFactory::class);
		$connectionFactory->method('PDOConnections')->willReturn([$connection]);

		$driverFactory = EngineFactory::create($connectionFactory);

		$config = new EngineConfig([
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

		$driver = $driverFactory->createEngine($config);
		$this->assertInstanceOf(EngineBase::class, $driver);
		$this->assertInstanceOf(MySQL::class, $driver);
	}

	#[Test]
	public function createDriverExceptionNoConnections(): void
	{
		$connectionFactory = ConnectionFactory::create();
		$driverFactory = EngineFactory::create($connectionFactory);

		$config = new EngineConfig([
			'driver' => 'mysql',
			'options' => [],
			'connections' => []
		]);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('No connections found in driver configuration');
		$this->expectExceptionCode(400);
		$driverFactory->createEngine($config);
	}

	#[Test]
	public function createDriverExceptionBadDriverName(): void
	{
		$connectionFactory = ConnectionFactory::create();
		$driverFactory = EngineFactory::create($connectionFactory);

		$config = new EngineConfig([
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
		$driverFactory->createEngine($config);
	}
}
