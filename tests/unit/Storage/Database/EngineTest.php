<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Database;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\MySQL;
use Projom\Storage\Database\Driver\DriverInterface;
use Projom\Storage\Database\Driver\Driver;
use Projom\Storage\Database\Engine;
use Projom\Storage\Database\Source\PDOSource;

class EngineTest extends TestCase
{
	public function test_setDriver()
	{
		$source = $this->createMock(PDOSource::class);		
		$mysql = MySQL::create($source);
		Engine::setDriver($mysql);
		$this->assertInstanceOf(DriverInterface::class, Engine::driver());
		$this->assertInstanceOf(MySQL::class, Engine::driver());
	}

	public function test_useDriver()
	{
		$source = $this->createMock(PDOSource::class);		
		$mysql = MySQL::create($source);
		Engine::setDriver($mysql);
		Engine::useDriver($mysql->type());
		$this->assertEquals($mysql->type(), Engine::driver()->type());
	}

	public function test_useDriver_exception()
	{
		$this->expectException(\Exception::class);
		Engine::clear();
		Engine::useDriver(Driver::MySQL);
	}

	public function test_clear()
	{
		$source = $this->createMock(PDOSource::class);		

		$mysql = MySQL::create($source);
		Engine::setDriver($mysql);
		Engine::clear();
		$this->assertNull(Engine::driver());
	}

	public function test_driver()
	{
		Engine::clear();
		$this->assertNull(Engine::driver());

		$source = $this->createMock(PDOSource::class);		
		$mysql = MySQL::create($source);
		Engine::setDriver($mysql);
		$this->assertInstanceOf(MySQL::class, Engine::driver());
	}
}