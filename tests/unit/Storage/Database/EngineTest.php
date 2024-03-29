<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Database;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\MySQL;
use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Drivers;
use Projom\Storage\Database\Engine;
use Projom\Storage\Database\PDO\Source;

class EngineTest extends TestCase
{
	public function test_loadMySQLDriver()
	{
		Engine::loadMySQLDriver([], []);
		$this->assertInstanceOf(DriverInterface::class, Engine::driver());
		$this->assertInstanceOf(MySQL::class, Engine::driver());
	}

	public function test_setDriver()
	{
		$mysql = MySQL::create(Source::create([], []));
		Engine::setDriver($mysql);
		$this->assertInstanceOf(DriverInterface::class, Engine::driver());
		$this->assertInstanceOf(MySQL::class, Engine::driver());
	}

	public function test_useDriver()
	{
		$mysql = MySQL::create(Source::create([], []));
		Engine::setDriver($mysql);
		Engine::useDriver($mysql->type());
		$this->assertEquals($mysql->type(), Engine::driver()->type());
	}

	public function test_useDriver_exception()
	{
		$this->expectException(\Exception::class);
		Engine::clear();
		Engine::useDriver(Drivers::MySQL);
	}

	public function test_clear()
	{
		$mysql = MySQL::create(Source::create([], []));
		Engine::setDriver($mysql);
		Engine::clear();
		$this->assertNull(Engine::driver());
	}

	public function test_driver()
	{
		Engine::clear();
		$this->assertNull(Engine::driver());

		$mysql = MySQL::create(Source::create([], []));
		Engine::setDriver($mysql);
		$this->assertInstanceOf(MySQL::class, Engine::driver());
	}
}