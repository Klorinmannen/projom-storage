<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage;

use PHPUnit\Framework\TestCase;

use Projom\Storage\Database;
use Projom\Storage\Database\Driver\MySQL;
use Projom\Storage\Database\Drivers;
use Projom\Storage\Database\Engine;
use Projom\Storage\Database\Query;

class DatabaseTest extends TestCase
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
		
		$database = Database::create(Drivers::MySQL);
		$this->assertInstanceOf(Database::class, $database);
		
		$query = $database->query('User');
		$this->assertInstanceOf(Query::class, $query);

		$result = $database->sql('SELECT * FROM User WHERE UserID = :user_id', [ 'user_id' => 10 ]);
		$this->assertEquals($record, $result);

		$database->clear();
		$this->expectException(\Exception::class);
		$database->query('User');
	}	
}