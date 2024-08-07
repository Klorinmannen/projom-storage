<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Engine\Driver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\Driver\MySQL;
use Projom\Storage\Database\Query\Action;
use Projom\Storage\Database\Query\Filter;
use Projom\Storage\Database\Query\LogicalOperator;
use Projom\Storage\Database\Query\QueryObject;

class MySQLTest extends TestCase
{
	#[Test]
	public function select(): void
	{
		$expected = [0 => ['UserID' => '10', 'Name' => 'John']];

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('fetchAll')->willReturn($expected);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($pdo);
		$queryObject = new QueryObject(
			collections: ['User'],
			fields: ['*']
		);

		$result = $mysql->dispatch(Action::SELECT, $queryObject);
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function update(): void
	{
		$expected = 6;

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('rowCount')->willReturn($expected);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($pdo);
		$queryObject = new QueryObject(
			collections: ['User'],
			fieldsWithValues: [['Name' => 'John']]
		);

		$result = $mysql->dispatch(Action::UPDATE, $queryObject);
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function insert(): void
	{
		$expected = '10';

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($pdoStatement);
		$pdo->expects($this->once())->method('lastInsertId')->willReturn($expected);

		$mysql = MySQL::create($pdo);
		$queryObject = new QueryObject(
			collections: ['User'],
			fieldsWithValues: [['Name' => 'John', 'Age' => 25]]
		);

		$result = $mysql->dispatch(Action::INSERT, $queryObject);
		$this->assertEquals((int) $expected, $result);
	}

	#[Test]
	public function delete(): void
	{
		$expected = 9;

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('rowCount')->willReturn($expected);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($pdo);
		$queryObject = new QueryObject(
			collections: ['User'],
			filters: [
				[
					Filter::buildGroup(['UserID' => 10, 'Name' => 'John']),
					LogicalOperator::AND
				]
			]
		);

		$result = $mysql->dispatch(Action::DELETE, $queryObject);
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function counts(): void
	{
		$expected = 3;

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('fetch')->willReturn([$expected]);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($pdo);
		$queryObject = new QueryObject(
			collections: ['User'],
			fields: ['*']
		);

		$result = $mysql->dispatch(Action::COUNT, $queryObject);
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function sum_int(): void
	{
		$expected = 30;

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('fetch')->willReturn([$expected]);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($pdo);
		$queryObject = new QueryObject(
			collections: ['User'],
			fields: ['Age']
		);

		$result = $mysql->dispatch(Action::SUM, $queryObject);
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function sum_float(): void
	{
		$expected = 123.33;

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('fetch')->willReturn([$expected]);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($pdo);
		$queryObject = new QueryObject(
			collections: ['User'],
			fields: ['Amount']
		);

		$result = $mysql->dispatch(Action::SUM, $queryObject);
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function avg(): void
	{
		$expected = 3102.5;

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('fetch')->willReturn([$expected]);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($pdo);
		$queryObject = new QueryObject(
			collections: ['User'],
			fields: ['Amount']
		);

		$result = $mysql->dispatch(Action::AVG, $queryObject);
		$this->assertEquals($expected, $result);

	}

	#[Test]
	public function max(): void
	{
		$expected = '1000';

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('fetch')->willReturn([$expected]);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($pdo);
		$queryObject = new QueryObject(
			collections: ['User'],
			fields: ['Score']
		);

		$result = $mysql->dispatch(Action::MAX, $queryObject);
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function min(): void
	{
		$expected = '0';

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('fetch')->willReturn([$expected]);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($pdo);
		$queryObject = new QueryObject(
			collections: ['User'],
			fields: ['Score']
		);

		$result = $mysql->dispatch(Action::MIN, $queryObject);
		$this->assertEquals($expected, $result);
	}

	public static function execute_provider(): array
	{
		return [
			[
				true,
				false,
				'Failed to execute statement'
			],
			[
				false,
				true,
				'Failed to prepare statement'
			]
		];
	}

	#[Test]
	public function execute_failed_to_prepare_statement(): void
	{
		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn(false);

		$mysql = new MySQL($pdo);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Failed to prepare statement');
		$this->expectExceptionCode(500);
		$mysql->dispatch(Action::EXECUTE, ['Select * FROM User', null]);
	}

	#[Test]
	public function execute_failed_to_execute_statement(): void
	{
		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->method('execute')->willReturn(false);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = new MySQL($pdo);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Failed to execute statement');
		$this->expectExceptionCode(500);
		$mysql->dispatch(Action::EXECUTE, ['Select * FROM User', null]);
	}

	public static function query_provider(): array
	{
		return [
			[
				'SELECT * FROM `User`',
				null,
				[0 => ['UserID' => '10', 'Name' => 'John']]
			],
			[
				'SELECT * FROM `User` WHERE `UserID` = ?',
				['10'],
				[0 => ['UserID' => '10', 'Name' => 'John']]
			],
		];
	}

	#[Test]
	#[DataProvider('query_provider')]
	public function query(string $sql, array|null $params, array $expected): void
	{
		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('fetchAll')->willReturn($expected);

		$pdo = $this->createMock(\PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($pdo);
		$query = $mysql->dispatch(Action::EXECUTE, [$sql, $params]);
		$this->assertEquals($expected, $query);
	}
}
