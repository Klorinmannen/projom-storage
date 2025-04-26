<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Static\Query;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Query\Action;
use Projom\Storage\Engine\Driver\Driver;
use Projom\Storage\Engine\Driver\MySQL as MySQLDriver;
use Projom\Storage\SQL\QueryBuilder;
use Projom\Storage\Static\Engine;
use Projom\Storage\Static\Query\DB;

class DBTest extends TestCase
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

		$query = DB::query('User');
		$this->assertInstanceOf(QueryBuilder::class, $query);
	}

	#[Test]
	public function query_missing_driver()
	{
		$this->expectExceptionCode(400);
		DB::query('User');
	}

	#[Test]
	public function execute()
	{
		$expected = [0 => ['id' => 1, 'name' => 'John']];

		$mysql = $this->createMock(MySQLDriver::class);
		$mysql->expects($this->once())->method('dispatch')->willReturn($expected);
		Engine::setDriver($mysql, Driver::MySQL);

		$actual = DB::execute(['SELECT * FROM User']);
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function runMethod()
	{
		$expected = [0 => ['id' => 1, 'name' => 'John']];

		$mysql = $this->createMock(MySQLDriver::class);
		$mysql->expects($this->once())->method('dispatch')->willReturn($expected);
		Engine::setDriver($mysql, Driver::MySQL);

		$actual = DB::run(Action::EXECUTE, ['SELECT * FROM User']);
		$this->assertEquals($expected, $actual);
	}
}
