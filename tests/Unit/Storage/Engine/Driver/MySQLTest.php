<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Engine\Driver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Action;
use Projom\Storage\Engine\Driver\MySQL;
use Projom\Storage\SQL\QueryObject;
use Projom\Storage\SQL\Util\Filter;
use Projom\Storage\SQL\Util\LogicalOperator;

class MySQLTest extends TestCase
{
	public static function selectProvider(): array
	{
		return [
			[
				[0 => ['UserID' => '10', 'Name' => 'John']],
				[0 => ['UserID' => '10', 'Name' => 'John']]
			],
			[
				null,
				[]
			]
		];
	}
	#[Test]
	#[DataProvider('selectProvider')]
	public function select(null|array $expected, array $records): void
	{
		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('fetchAll')->willReturn($records);

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
	public function executeFailedToPrepareStatement(): void
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
	public function executeFailedToExecuteStatement(): void
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

	public static function queryProvider(): array
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
	#[DataProvider('queryProvider')]
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
