<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\Driver\MySQL;
use Projom\Storage\Database\Engine\Driver;
use Projom\Storage\Database\Engine;
use Projom\Storage\Database\Engine\DriverFactory;
use Projom\Storage\Database\Query\Action;

class EngineTest extends TestCase
{
	public function setup(): void
	{
		Engine::clear();
	}

	#[Test]
	public function dispatch_no_driver_exception(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage("Engine driver not set");
		$this->expectExceptionCode(400);
		Engine::dispatch(Action::QUERY, 'table');
	}

	#[Test]
	public function dispatch_invalid_dispatch_exception(): void
	{
		$mysql = $this->createMock(MySQL::class);
		Engine::setDriver($mysql, Driver::MySQL);

		$this->expectNotToPerformAssertions();

		$actions = Action::cases();
		foreach ($actions as $action) {

			$value = null;
			if ($action === Action::EXECUTE)
				$value = ['query', ['params']];

			Engine::dispatch($action, $value);
		}
	}

	#[Test]
	public function driver(): void
	{
		$pdo = $this->createMock(\PDO::class);
		$mysql = MySQL::create($pdo);
		Engine::setDriver($mysql, Driver::MySQL);

		$this->assertInstanceOf(MySQL::class, Engine::driver());
	}

	#[Test]
	public function useDriver(): void
	{
		$pdo = $this->createMock(\PDO::class);
		$mysql = MySQL::create($pdo);
		Engine::setDriver($mysql, Driver::MySQL);
		Engine::useDriver(Driver::MySQL);
		$this->assertInstanceOf(MySQL::class, Engine::driver());
	}

	#[Test]
	public function useDriver_exception(): void
	{
		Engine::clear();

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage("Driver not loaded");
		$this->expectExceptionCode(400);
		Engine::useDriver(Driver::MySQL);
	}

	#[Test]
	public function loadDriver(): void
	{
		$pdo = $this->createMock(\PDO::class);
		$mysql = MySQL::create($pdo);

		$driverFactory = $this->createMock(DriverFactory::class);
		$driverFactory->expects($this->once())
			->method('createDriver')
			->willReturn($mysql);

		Engine::setDriverFactory($driverFactory);
		Engine::loadDriver(['driver' => 'mysql']);

		$this->assertInstanceOf(MySQL::class, Engine::driver());
	}

	#[Test]
	public function loadDriver_driver_factory_not_set_exception(): void
	{
		Engine::clear();

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage("Driver factory not set");
		$this->expectExceptionCode(400);
		Engine::loadDriver(['driver' => 'mysql']);
	}

	#[Test]
	public function setDriver(): void
	{
		$pdo = $this->createMock(\PDO::class);
		$mysql = MySQL::create($pdo);
		Engine::setDriver($mysql, Driver::MySQL);
		$this->assertInstanceOf(MySQL::class, Engine::driver());
	}

	#[Test]
	public function start(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('DNS missing server host');

		Engine::start();
		Engine::loadDriver(['driver' => 'mysql']);
	}

	#[Test]
	public function clear(): void
	{
		$pdo = $this->createMock(\PDO::class);
		$mysql = MySQL::create($pdo);
		Engine::setDriver($mysql, Driver::MySQL);

		Engine::clear();

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage("Engine driver not set");
		$this->expectExceptionCode(400);

		Engine::driver();
	}
}
