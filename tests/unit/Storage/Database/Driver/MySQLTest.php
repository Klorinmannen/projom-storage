<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\MySQL;
use Projom\Storage\Database\Driver\Driver;
use Projom\Storage\Database\Query\LogicalOperator;
use Projom\Storage\Database\Query\Operator;
use Projom\Storage\Database\Source\PDOSource;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\QueryObject;

class MySQLTest extends TestCase
{
	public function test_type(): void
	{
		$source = $this->createMock(PDOSource::class);
		$mysql = new MySQL($source);

		$this->assertEquals(Driver::MySQL, $mysql->type());
	}

	public function test_select(): void
	{
		$expected = [['UserID' => '10', 'Name' => 'John']];

		$source = $this->createMock(PDOSource::class);
		$source->expects($this->once())->method('run');
		$source->expects($this->once())->method('fetchResult')->willReturn($expected);

		$mysql = MySQL::create($source);
		$queryObject = new QueryObject(collections: ['User'], fields: ['*']);

		$result = $mysql->select($queryObject);
		$this->assertEquals($expected, $result);
	}

	public function test_update(): void
	{
		$expected = 6;

		$source = $this->createMock(PDOSource::class);
		$source->expects($this->once())->method('run');
		$source->expects($this->once())->method('rowsAffected')->willReturn($expected);

		$mysql = MySQL::create($source);
		$queryObject = new QueryObject(collections: ['User'], fieldsWithValues: ['Name' => 'John']);

		$result = $mysql->update($queryObject);
		$this->assertEquals($expected, $result);
	}

	public function test_insert(): void
	{
		$expected = 10;

		$source = $this->createMock(PDOSource::class);
		$source->expects($this->once())->method('run');
		$source->expects($this->once())->method('insertedID')->willReturn($expected);

		$mysql = MySQL::create($source);
		$queryObject = new QueryObject(collections: ['User'], fieldsWithValues: ['Name' => 'John', 'Age' => 25]);

		$result = $mysql->insert($queryObject);
		$this->assertEquals($expected, $result);
	}

	public function test_delete(): void
	{
		$expected = 1;

		$source = $this->createMock(PDOSource::class);
		$source->expects($this->once())->method('run');
		$source->expects($this->once())->method('rowsAffected')->willReturn($expected);

		$mysql = MySQL::create($source);
		$queryObject = new QueryObject(collections: ['User'], filters: [['UserID', Operator::EQ, '10', LogicalOperator::AND]]);

		$result = $mysql->delete($queryObject);
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
