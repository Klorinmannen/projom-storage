<?php

declare(strict_types=1);

namespace JRF\Tests\Unit\Storage\Internal\Engine;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use JRF\Storage\Internal\Engine\Connection\ConnectionFactory;
use JRF\Storage\Internal\Engine\Connection\PDOConnection;
use JRF\Storage\Internal\Engine\EngineConfig;
use JRF\Storage\Internal\Engine\EngineFactory;
use JRF\Storage\Internal\Engine\EngineBase;
use JRF\Storage\Internal\Engine\MySQL;

class EngineFactoryTest extends TestCase
{
	#[Test]
	public function createEngine(): void
	{
		$connection = $this->createMock(PDOConnection::class);
		$connection->method('name')->willReturn('name');

		$connectionFactory = $this->createStub(ConnectionFactory::class);
		$connectionFactory->method('PDOConnections')->willReturn([$connection]);

		$engineFactory = EngineFactory::create($connectionFactory);

		$config = new EngineConfig([
			'engine' => 'mysql',
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

		$engine = $engineFactory->createEngine($config);
		$this->assertInstanceOf(EngineBase::class, $engine);
		$this->assertInstanceOf(MySQL::class, $engine);
	}

	#[Test]
	public function createEngineExceptionNoConnections(): void
	{
		$connectionFactory = ConnectionFactory::create();
		$engineFactory = EngineFactory::create($connectionFactory);

		$config = new EngineConfig([
			'engine' => 'mysql',
			'options' => [],
			'connections' => []
		]);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('No connections found in engine configuration');
		$this->expectExceptionCode(400);
		$engineFactory->createEngine($config);
	}

	#[Test]
	public function createEngineExceptionBadEngineName(): void
	{
		$connectionFactory = ConnectionFactory::create();
		$engineFactory = EngineFactory::create($connectionFactory);

		$config = new EngineConfig([
			'engine' => 'bad-engine-name',
			'options' => [],
			'connections' => [
				'nicedbname' => [
					'dsn' => 'sqlite::memory:'
				]
			]
		]);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Engine is not supported');
		$this->expectExceptionCode(400);
		$engineFactory->createEngine($config);
	}
}
