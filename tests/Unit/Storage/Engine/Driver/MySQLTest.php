<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Engine\Driver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Query\Action;
use Projom\Storage\Engine\Driver\ConnectionInterface;
use Projom\Storage\Engine\Driver\MySQL;
use Projom\Storage\Engine\Driver\PDOConnection;
use Projom\Storage\Query\Format;
use Projom\Storage\SQL\QueryObject;
use Projom\Storage\SQL\Util\Filter;
use Projom\Storage\SQL\Util\LogicalOperator;

class FakePDOConnection implements ConnectionInterface {}

class MySQLTest extends TestCase
{
	#[Test]
	public function setConnectionException(): void
	{
		$fakeConnection = new FakePDOConnection();
		$mysql = MySQL::create();

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Provided connection is not a PDO connection');
		$this->expectExceptionCode(400);
		$mysql->setConnection($fakeConnection, 'fake');
	}

	#[Test]
	public function changeConnectionException(): void
	{
		$mysql = MySQL::create();
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage("Connection: 'fake' does not exist.");
		$this->expectExceptionCode(400);
		$mysql->dispatch(Action::CHANGE_CONNECTION, 'fake');
	}

	public static function selectProvider(): array
	{
		return [
			[
				[['UserID' => '10', 'Name' => 'John']],
				(object)['UserID' => '10', 'Name' => 'John']
			],
			[
				[],
				null
			]
		];
	}
	#[Test]
	#[DataProvider('selectProvider')]
	public function select(array $records, null|object $expected): void
	{
		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('fetchAll')->willReturn($records);

		$connection = $this->createMock(PDOConnection::class);
		$connection->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($connection);
		$queryObject = new QueryObject(
			collections: ['User'],
			fields: ['*'],
			formatting: [Format::STD_CLASS]
		);

		$mysql->setOptions(['return_single_record' => true]);

		$actual = $mysql->dispatch(Action::SELECT, $queryObject);
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function update(): void
	{
		$expected = 6;

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('rowCount')->willReturn($expected);

		$connection = $this->createMock(PDOConnection::class);
		$connection->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($connection);
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

		$connection = $this->createMock(PDOConnection::class);
		$connection->expects($this->once())->method('prepare')->willReturn($pdoStatement);
		$connection->expects($this->once())->method('lastInsertId')->willReturn($expected);

		$mysql = MySQL::create($connection);
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

		$connection = $this->createMock(PDOConnection::class);
		$connection->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($connection);
		$queryObject = new QueryObject(
			collections: ['User'],
			filters: [
				[
					Filter::list(['UserID' => 10, 'Name' => 'John']),
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
		$connection = $this->createMock(PDOConnection::class);
		$connection->expects($this->once())->method('prepare')->willReturn(false);

		$mysql = MySQL::create($connection);

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

		$connection = $this->createMock(PDOConnection::class);
		$connection->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($connection);

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

		$connection = $this->createMock(PDOConnection::class);
		$connection->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($connection);
		$query = $mysql->dispatch(Action::EXECUTE, [$sql, $params]);
		$this->assertEquals($expected, $query);
	}
}
