<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Query\Action;
use Projom\Storage\Engine;
use Projom\Storage\Engine\Driver\Driver;
use Projom\Storage\Engine\Driver\DriverFactory;
use Projom\Storage\Engine\Driver\MySQL;
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
		$this->expectNotToPerformAssertions();

		$mysql = $this->createMock(MySQL::class);
		$mysql->method('dispatch')->willReturn([]);
		Engine::setDriver($mysql, Driver::MySQL);

		$actions = Action::cases();
		foreach ($actions as $action) {

			$value = null;
			if ($action === Action::EXECUTE)
				$value = ['query', ['params']];
			elseif ($action ===  Action::QUERY)
				$value = [['User'], null];
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
		$mysql = $this->createMock(MySQL::class);
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
		$mysql = $this->createMock(MySQL::class);
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
		$driver = $this->createMock(MySQL::class);
		Engine::setDriver($driver, Driver::MySQL);
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
