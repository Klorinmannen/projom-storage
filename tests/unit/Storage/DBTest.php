<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage;

use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\MySQL;
use Projom\Storage\Database\Drivers;
use Projom\Storage\Database\Engine;
use Projom\Storage\Database\Query;
use Projom\Storage\DB;

class DBTest extends TestCase
{
	public function test()
	{
		$record = [ 1 => 'record' ];

		$mysql = $this->createMock(MySQL::class);
		$mysql->method('type')->willReturn(Drivers::MySQL);
		$mysql->method('select')->willReturn($record);
		$mysql->method('Query')->willReturn($this->createMock(Query::class));
		$mysql->method('execute')->willReturn($record);

		Engine::setDriver($mysql);

		$query = DB::query('User');
		$this->assertInstanceOf(Query::class, $query);

		$result = DB::sql('SELECT * FROM User');
		$this->assertEquals($record, $result);
	}	
}