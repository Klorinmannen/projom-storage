<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Static\MySQL;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine\Driver\Driver;
use Projom\Storage\Engine\Driver\MySQL as MySQLDriver;
use Projom\Storage\SQL\Statement\Builder;
use Projom\Storage\Static\MySQL\Query;
use Projom\Storage\Static\Engine;

class QueryTest extends TestCase
{
	public function setUp(): void
	{
		Engine::clear();
	}

	#[Test]
	public function query()
	{
		$mysql = $this->createMock(MySQLDriver::class);
		$mysql->expects($this->once())->method('dispatch')->willReturn(Builder::create());
		Engine::setDriver($mysql, Driver::MySQL);

		$query = Query::build('User');
		$this->assertInstanceOf(Builder::class, $query);
	}

	#[Test]
	public function queryMissingDriver()
	{
		$this->expectExceptionCode(400);
		$this->expectExceptionMessage('Driver not loaded');
		Query::build('User');
	}

	#[Test]
	public function sql()
	{
		$expected = [0 => ['id' => 1, 'name' => 'John']];

		$mysql = $this->createMock(MySQLDriver::class);
		$mysql->expects($this->once())->method('dispatch')->willReturn($expected);
		Engine::setDriver($mysql, Driver::MySQL);

		$actual = Query::sql('SELECT * FROM User');
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function useConnection()
	{
		$mysql = $this->createMock(MySQLDriver::class);
		$mysql->expects($this->once())->method('dispatch')->willReturn(true);
		Engine::setDriver($mysql, Driver::MySQL);
		Query::useConnection('default');
	}

	#[Test]
	public function startTransaction()
	{
		$mysql = $this->createMock(MySQLDriver::class);
		$mysql->expects($this->once())->method('dispatch')->willReturn(true);
		Engine::setDriver($mysql, Driver::MySQL);
		Query::startTransaction();
	}

	#[Test]
	public function endTransaction()
	{
		$mysql = $this->createMock(MySQLDriver::class);
		$mysql->expects($this->once())->method('dispatch')->willReturn(true);
		Engine::setDriver($mysql, Driver::MySQL);
		Query::endTransaction();
	}

	#[Test]
	public function revertTransaction()
	{
		$mysql = $this->createMock(MySQLDriver::class);
		$mysql->expects($this->once())->method('dispatch')->willReturn(true);
		Engine::setDriver($mysql, Driver::MySQL);
		Query::revertTransaction();
	}
}
