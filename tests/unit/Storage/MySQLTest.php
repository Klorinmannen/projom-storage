<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine;
use Projom\Storage\Engine\Driver;
use Projom\Storage\Engine\Driver\MySQL as MySQLDriver;
use Projom\Storage\MySQL;
use Projom\Storage\SQL\QueryBuilder;

class MySQLTest extends TestCase
{
	public function setUp(): void
	{
		Engine::clear();
	}
	
	#[Test]
	public function query()
	{
		$mysql = $this->createMock(MySQLDriver::class);
		$mysql->expects($this->once())->method('dispatch')->willReturn(QueryBuilder::create());
		Engine::setDriver($mysql, Driver::MySQL);

		$query = MySQL::query('User');
		$this->assertInstanceOf(QueryBuilder::class, $query);
	}

	#[Test]
	public function query_missing_driver()
	{
		$this->expectExceptionCode(400);
		MySQL::query('User');
	}

	#[Test]
	public function sql()
	{
		$expected = [0 => [ 'id' => 1, 'name' => 'John' ]];

		$mysql = $this->createMock(MySQLDriver::class);
		$mysql->expects($this->once())->method('dispatch')->willReturn($expected);
		Engine::setDriver($mysql, Driver::MySQL);

		$actual = MySQL::sql('SELECT * FROM User');
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function startTransaction()
	{
		$mysql = $this->createMock(MySQLDriver::class);
		$mysql->expects($this->once())->method('dispatch')->willReturn(true);
		Engine::setDriver($mysql, Driver::MySQL);
		MySQL::startTransaction();
	}

	#[Test]
	public function endTransaction()
	{
		$mysql = $this->createMock(MySQLDriver::class);
		$mysql->expects($this->once())->method('dispatch')->willReturn(true);
		Engine::setDriver($mysql, Driver::MySQL);
		MySQL::endTransaction();
	}

	#[Test]
	public function revertTransaction()
	{
		$mysql = $this->createMock(MySQLDriver::class);
		$mysql->expects($this->once())->method('dispatch')->willReturn(true);
		Engine::setDriver($mysql, Driver::MySQL);
		MySQL::revertTransaction();
	}
}
