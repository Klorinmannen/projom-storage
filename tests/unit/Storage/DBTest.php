<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\MySQLDriver;
use Projom\Storage\Database\Engine;
use Projom\Storage\Database\Engine\Driver;
use Projom\Storage\Database\Query;
use Projom\Storage\DB;

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
		Engine::setDriver($mysql, Driver::MySQL);

		$query = DB::query('User');
		$this->assertInstanceOf(Query::class, $query);
	}

	#[Test]
	public function query_missing_driver()
	{
		$this->expectExceptionCode(400);
		DB::query('User');
	}

	#[Test]
	public function sql()
	{
		$expected = [0 => [ 'id' => 1, 'name' => 'John' ]];

		$mysql = $this->createMock(MySQLDriver::class);
		$mysql->expects($this->once())->method('query')->willReturn($expected);
		Engine::setDriver($mysql, Driver::MySQL);

		$actual = DB::sql('SELECT * FROM User');
		$this->assertEquals($expected, $actual);
	}
}
