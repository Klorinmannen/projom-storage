<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Query\Action;
use Projom\Storage\Engine;
use Projom\Storage\Engine\Driver;
use Projom\Storage\Engine\DriverFactory;
use Projom\Storage\Engine\Driver\MySQL;
use Projom\Storage\Engine\Driver\PDOConnection;
use Projom\Storage\SQL\QueryObject;

class EngineTest extends TestCase
{
	public function setup(): void
	{
		Engine::clear();
	}

	#[Test]
	public function dispatchNoDriverException(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage("Engine driver not set");
		$this->expectExceptionCode(400);
		Engine::dispatch(Action::QUERY, args: ['User']);
	}

	#[Test]
	public function dispatch(): void
	{
		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->atLeastOnce())->method('execute')->willReturn(true);

		$connection = $this->createMock(PDOConnection::class);	
		$connection->expects($this->atLeastOnce())->method('prepare')->willReturn($pdoStatement);
		$connection->expects($this->atLeastOnce())->method('name')->willReturn('default');

		$mysql = MySQL::create($connection);
		Engine::setDriver($mysql, Driver::MySQL);

		$actions = Action::cases();
		foreach ($actions as $action) {

			$value = null;
			if ($action === Action::EXECUTE)
				$value = ['query', ['params']];
			elseif ($action ===  Action::QUERY)
				$value = [ 'User' ];
			elseif ($action === Action::CHANGE_CONNECTION)
				$value = 'default';
			else 
				$value = new QueryObject(collections: ['User'], fields: ['Name']);

			Engine::dispatch($action, args: $value);
		}
	}

	#[Test]
	public function useDriver(): void
	{
		$this->expectNotToPerformAssertions();

		$connection = $this->createMock(PDOConnection::class);
		$mysql = MySQL::create($connection);
		Engine::setDriver($mysql, Driver::MySQL);
		Engine::useDriver(Driver::MySQL);
	}

	#[Test]
	public function useDriverException(): void
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

		$connection = $this->createMock(PDOConnection::class);
		$mysql = MySQL::create($connection);

		$driverFactory = $this->createMock(DriverFactory::class);
		$driverFactory->method('createDriver')->willReturn($mysql);

		Engine::setDriverFactory($driverFactory);
		Engine::loadDriver(['driver' => 'mysql']);
	}

	#[Test]
	public function loadDriverDriverFactoryNotSetException(): void
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

		$connection = $this->createMock(PDOConnection::class);
		$mysql = MySQL::create($connection);
		Engine::setDriver($mysql, Driver::MySQL);
	}

	#[Test]
	public function start(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('No connections found in driver configuration');

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
