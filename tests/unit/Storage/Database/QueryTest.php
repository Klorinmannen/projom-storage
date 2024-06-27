<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\MySQLDriver;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\LogicalOperator;
use Projom\Storage\Database\Query\Operator;

class QueryTest extends TestCase
{
	#[Test]
	public function fetch_select_get(): void
	{
		$expected = [0 => ['Name' => 'John', 'Age' => 25]];

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->atLeastOnce())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->atLeastOnce())->method('fetchAll')->willReturn($expected);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->atLeastOnce())->method('prepare')->willReturn($pdoStatement);

		$driver = MySQLDriver::create($pdo);
		$query = Query::create($driver, ['User']);

		$result = $query->fetch('Name', 'John', Operator::EQ);
		$this->assertEquals($expected, $result);

		$result = $query->select('Name', 'Age');
		$this->assertEquals($expected, $result);

		$result = $query->get('Name', 'Age');
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function update_modify(): void
	{
		$expected = 1;

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('rowCount')->willReturn($expected);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$driver = MySQLDriver::create($pdo);
		$query = Query::create($driver, ['User']);

		$result = $query->update(['Name' => 'Jane', 'Age' => 21]);
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function insert_add(): void
	{
		$expected = '1';

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($pdoStatement);
		$pdo->expects($this->once())->method('lastInsertId')->willReturn($expected);

		$driver = MySQLDriver::create($pdo);
		$query = Query::create($driver, ['User']);

		$result = $query->insert(['Name' => 'Jane', 'Age' => 21]);
		$this->assertEquals((int) $expected, $result);
	}

	#[Test]
	public function delete_remove(): void
	{
		$expected = 2;

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('rowCount')->willReturn($expected);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$driver = MySQLDriver::create($pdo);
		$query = Query::create($driver, ['User']);

		$result = $query->delete();
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function filterOn(): void
	{
		$pdo = $this->createMock(\PDO::class);

		$driver = MySQLDriver::create($pdo);
		$query = Query::create($driver, ['User']);

		$query = $query->filterOn(
			['Name' => ['John', 'Jane']],
			Operator::IN,
			LogicalOperator::OR
		)->filterOn(
			['Age' => null],
			Operator::IS_NOT_NULL
		);
		$this->assertInstanceOf(Query::class, $query);
	}

	#[Test]
	public function sortOn(): void
	{
		$pdo = $this->createMock(\PDO::class);

		$driver = MySQLDriver::create($pdo);
		$query = Query::create($driver, ['User']);

		$query = $query->sortOn(['Name' => 'ASC'])->sortOn(['Age' => 'DESC']);
		$this->assertInstanceOf(Query::class, $query);
	}

	#[Test]
	public function limit(): void
	{
		$pdo = $this->createMock(\PDO::class);

		$driver = MySQLDriver::create($pdo);
		$query = Query::create($driver, ['User']);

		$query = $query->limit(10);
		$this->assertInstanceOf(Query::class, $query);
	}
}
