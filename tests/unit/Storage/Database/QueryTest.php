<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\Driver\MySQL;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\Join;
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

		$driver = MySQL::create($pdo);
		$query = Query::create($driver, ['User']);

		$result = $query->fetch('Name', 'John', Operator::EQ);
		$this->assertEquals($expected, $result);

		$result = $query->select('Name', 'Age');
		$this->assertEquals($expected, $result);

		$result = $query->get('Name', 'Age');
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function counts(): void
	{
		$expected = 2;

		$driver = $this->createMock(MySQL::class);
		$driver->expects($this->once())->method('dispatch')->willReturn($expected);		
		$query = Query::create($driver, ['User']);

		$result = $query->count();
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function sum(): void
	{
		$expected = 3301653;

		$driver = $this->createMock(MySQL::class);
		$driver->expects($this->once())->method('dispatch')->willReturn($expected);
		$query = Query::create($driver, ['Invoice']);

		$result = $query->sum('Amount');
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function avg(): void
	{
		$expected = 165082.65;

		$driver = $this->createMock(MySQL::class);
		$driver->expects($this->once())->method('dispatch')->willReturn($expected);
		$query = Query::create($driver, ['Invoice']);

		$result = $query->avg('Amount');
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function max(): void
	{
		$expected = '2024-01-01';

		$driver = $this->createMock(MySQL::class);
		$driver->expects($this->once())->method('dispatch')->willReturn($expected);
		$query = Query::create($driver, ['Invoice']);

		$result = $query->max('Created');
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function min(): void
	{
		$expected = '2019-01-01';

		$driver = $this->createMock(MySQL::class);
		$driver->expects($this->once())->method('dispatch')->willReturn($expected);
		$query = Query::create($driver, ['Invoice']);

		$result = $query->min('Created');
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function update_modify(): void
	{
		$expected = 1;

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->atLeastOnce())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->atLeastOnce())->method('rowCount')->willReturn($expected);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->atLeastOnce())->method('prepare')->willReturn($pdoStatement);

		$driver = MySQL::create($pdo);
		$query = Query::create($driver, ['User']);

		$result = $query->update(['Name' => 'Jane', 'Age' => 21]);
		$this->assertEquals($expected, $result);

		$result = $query->modify(['Name' => 'Jane', 'Age' => 21]);
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function insert_add(): void
	{
		$expected = '1';

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->atLeastOnce())->method('execute')->willReturn(true);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->atLeastOnce())->method('prepare')->willReturn($pdoStatement);
		$pdo->expects($this->atLeastOnce())->method('lastInsertId')->willReturn($expected);

		$driver = MySQL::create($pdo);
		$query = Query::create($driver, ['User']);

		$result = $query->insert(['Name' => 'Jane', 'Age' => 21]);
		$this->assertEquals((int) $expected, $result);

		$result = $query->add(['Name' => 'Jane', 'Age' => 21]);
		$this->assertEquals((int) $expected, $result);

		$result = $query->insertMultiple([['Name' => 'Jane', 'Age' => 21]]);
		$this->assertEquals((int) $expected, $result);

		$result = $query->addMultiple([['Name' => 'Jane', 'Age' => 21]]);
		$this->assertEquals((int) $expected, $result);
	}

	#[Test]
	public function delete_destroy(): void
	{
		$expected = 2;

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->atLeastOnce())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->atLeastOnce())->method('rowCount')->willReturn($expected);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->atLeastOnce())->method('prepare')->willReturn($pdoStatement);

		$driver = MySQL::create($pdo);
		$query = Query::create($driver, ['User']);

		$result = $query->delete();
		$this->assertEquals($expected, $result);

		$result = $query->destroy();
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function joinOn(): void
	{
		$pdo = $this->createMock(\PDO::class);

		$driver = MySQL::create($pdo);
		$query = Query::create($driver, ['User']);

		$query = $query->joinOn('User.UserID = UserRole.UserID', Join::INNER)
			->joinOn('UserRole.Role, UserAccess.Role', Join::INNER);
		$this->assertInstanceOf(Query::class, $query);
	}

	#[Test]
	public function filterOnGroup(): void
	{
		$pdo = $this->createMock(\PDO::class);

		$driver = MySQL::create($pdo);
		$query = Query::create($driver, ['User']);

		$query = $query->filterOnGroup([['Name', Operator::IN, ['John', 'Jane']]], LogicalOperator::AND)
			->filterOnGroup([['Age', Operator::IS_NOT_NULL, null]], LogicalOperator::OR);
		$this->assertInstanceOf(Query::class, $query);
	}

	#[Test]
	public function filterOnList(): void
	{
		$pdo = $this->createMock(\PDO::class);

		$driver = MySQL::create($pdo);
		$query = Query::create($driver, ['User']);

		$query = $query->filterOnList(['Name' => ['John', 'Jane']], Operator::IN, LogicalOperator::AND)
			->filterOnList(['Age' => null], Operator::IS_NOT_NULL, LogicalOperator::OR);
		$this->assertInstanceOf(Query::class, $query);
	}

	#[Test]
	public function filterOn(): void
	{
		$pdo = $this->createMock(\PDO::class);

		$driver = MySQL::create($pdo);
		$query = Query::create($driver, ['User']);

		$query = $query->filterOn('Name', ['John', 'Jane'], Operator::IN, LogicalOperator::AND)
			->filterOn('Age', null, Operator::IS_NOT_NULL, LogicalOperator::OR);
		$this->assertInstanceOf(Query::class, $query);
	}

	#[Test]
	public function groupOn(): void
	{
		$pdo = $this->createMock(\PDO::class);

		$driver = MySQL::create($pdo);
		$query = Query::create($driver, ['User']);

		$query = $query->groupOn('Username')->groupOn('Name', 'Age')->groupOn('Lastname, Age');
		$this->assertInstanceOf(Query::class, $query);
	}

	#[Test]
	public function sortOn(): void
	{
		$pdo = $this->createMock(\PDO::class);

		$driver = MySQL::create($pdo);
		$query = Query::create($driver, ['User']);

		$query = $query->sortOn(['Name' => 'ASC'])->sortOn(['Age' => 'DESC']);
		$this->assertInstanceOf(Query::class, $query);
	}

	#[Test]
	public function limit(): void
	{
		$pdo = $this->createMock(\PDO::class);

		$driver = MySQL::create($pdo);
		$query = Query::create($driver, ['User']);

		$query = $query->limit(10);
		$this->assertInstanceOf(Query::class, $query);
	}
}
