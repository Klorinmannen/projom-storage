<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\Driver\MySQL;
use Projom\Storage\Database\Engine\Driver;
use Projom\Storage\Database\Engine;
use Projom\Storage\Database\Engine\Config;
use Projom\Storage\Database\Engine\DriverFactory;
use Projom\Storage\Database\Query\Action;
use Projom\Storage\Database\Query\QueryObject;

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
		Engine::dispatch(Action::QUERY, 'User');
	}

	#[Test]
	public function dispatch(): void
	{
		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->atLeastOnce())->method('execute')->willReturn(true);

		$pdo = $this->createMock(\PDO::class);	
		$pdo->expects($this->atLeastOnce())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($pdo);
		Engine::setDriver($mysql, Driver::MySQL);

		$actions = Action::cases();
		foreach ($actions as $action) {

			$value = null;
			if ($action === Action::EXECUTE)
				$value = ['query', ['params']];
			elseif ($action ===  Action::QUERY)
				$value = [ 'User' ];
			else 
				$value = new QueryObject(collections: ['User']);

			Engine::dispatch($action, $value);
		}
	}

	#[Test]
	public function useDriver(): void
	{
		$this->expectNotToPerformAssertions();

		$pdo = $this->createMock(\PDO::class);
		$mysql = MySQL::create($pdo);
		Engine::setDriver($mysql, Driver::MySQL);
		Engine::useDriver(Driver::MySQL);
	}

	#[Test]
	public function useDriver_exception(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage("Driver not loaded");
		$this->expectExceptionCode(400);
		Engine::useDriver(Driver::MySQL);
	}

	#[Test]
	public function loadDriver(): void
	{
		$this->expectNotToPerformAssertions();

		$pdo = $this->createMock(\PDO::class);
		$mysql = MySQL::create($pdo);

		$driverFactory = $this->createMock(DriverFactory::class);
		$driverFactory->method('createDriver')->willReturn($mysql);

		Engine::setDriverFactory($driverFactory);
		Engine::loadDriver(['driver' => 'mysql']);
	}

	#[Test]
	public function loadDriver_driver_factory_not_set_exception(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage("Driver factory not set");
		$this->expectExceptionCode(400);
		Engine::loadDriver(['driver' => 'mysql']);
	}

	#[Test]
	public function setDriver(): void
	{
		$this->expectNotToPerformAssertions();

		$pdo = $this->createMock(\PDO::class);
		$mysql = MySQL::create($pdo);
		Engine::setDriver($mysql, Driver::MySQL);
	}

	#[Test]
	public function start(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Config is missing host');

		Engine::start();
		Engine::loadDriver(['driver' => 'mysql']);
	}

	#[Test]
	public function clear(): void
	{
		$this->expectNotToPerformAssertions();
		Engine::clear();
	}
}
