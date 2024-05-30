<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\MySQL;
use Projom\Storage\Database\Drivers;
use Projom\Storage\Database\LogicalOperators;
use Projom\Storage\Database\Operators;
use Projom\Storage\Database\Source\PDOSource;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\Delete;
use Projom\Storage\Database\Query\Insert;
use Projom\Storage\Database\Query\Select;
use Projom\Storage\Database\Query\Update;

class MySQLTest extends TestCase
{
	public function test_type(): void
	{
		$source = $this->createMock(PDOSource::class);
		$mysql = new MySQL($source);

		$this->assertEquals(Drivers::MySQL, $mysql->type());
	}

	public function test_select(): void
	{
		$expected = [['UserID' => '10', 'Name' => 'John']];

		$source = $this->createMock(PDOSource::class);
		$source->expects($this->once())->method('run');
		$source->expects($this->once())->method('fetchResult')->willReturn($expected);

		$mysql = MySQL::create($source);
		$querySelect = new Select(['User'], ['*']);

		$result = $mysql->select($querySelect);
		$this->assertEquals($expected, $result);
	}

	public function test_update(): void
	{
		$expected = 6;

		$source = $this->createMock(PDOSource::class);
		$source->expects($this->once())->method('run');
		$source->expects($this->once())->method('rowsAffected')->willReturn($expected);

		$mysql = MySQL::create($source);
		$queryUpdate = new Update(['User'], ['Name' => 'John']);

		$result = $mysql->update($queryUpdate);
		$this->assertEquals($expected, $result);
	}

	public function test_insert(): void
	{
		$expected = 10;

		$source = $this->createMock(PDOSource::class);
		$source->expects($this->once())->method('run');
		$source->expects($this->once())->method('insertedID')->willReturn($expected);

		$mysql = MySQL::create($source);
		$queryInsert = new Insert(['User'], ['Name' => 'John', 'Age' => 25]);

		$result = $mysql->insert($queryInsert);
		$this->assertEquals($expected, $result);
	}

	public function test_delete(): void
	{
		$expected = 1;

		$source = $this->createMock(PDOSource::class);
		$source->expects($this->once())->method('run');
		$source->expects($this->once())->method('rowsAffected')->willReturn($expected);

		$mysql = MySQL::create($source);
		$queryDelete = new Delete(['User'], [['UserID', Operators::EQ, '10', LogicalOperators::AND]]);

		$result = $mysql->delete($queryDelete);
		$this->assertEquals($expected, $result);
	}

	public static function query_test_provider(): array
	{
		return [
			[['User']],
			[['User', 'UserRole']]
		];
	}

	#[DataProvider('query_test_provider')]
	public function test_query(array $tables): void
	{
		$source = $this->createMock(PDOSource::class);
		$mysql = new MySQL($source);
		$query = $mysql->Query(...$tables);
		$this->assertInstanceOf(Query::class, $query);
	}

	public static function execute_test_provider(): array
	{
		return [
			['INSERT INTO `User` (`Name`) VALUES (?)', ['John'], []],
			['SELECT * FROM `User`', null, [['UserID' => '10', 'Name' => 'John']]]
		];
	}

	#[DataProvider('execute_test_provider')]
	public function test_execute(string $sql, array|null $params, array $expected): void
	{
		$source = $this->createMock(PDOSource::class);
		$source->expects($this->once())->method('fetchResult')->willReturn($expected);
		$mysql = new MySQL($source);
		$result = $mysql->execute($sql, $params);
		$this->assertEquals($expected, $result);
	}
}
