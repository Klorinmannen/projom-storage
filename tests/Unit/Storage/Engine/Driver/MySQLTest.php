<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Engine\Driver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Query\Action;
use Projom\Storage\Engine\Driver\Connection\ConnectionInterface;
use Projom\Storage\Engine\Driver\MySQL;
use Projom\Storage\Engine\Driver\Connection\PDOConnection;
use Projom\Storage\Query\Format;
use Projom\Storage\SQL\Statement;
use Projom\Storage\SQL\Statement\DTO;

class FakePDOConnection implements ConnectionInterface
{
	public function name(): int|string
	{
		return 1;
	}
}

class MySQLTest extends TestCase
{
	#[Test]
	public function setConnectionException(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Provided connection is not a PDO connection');
		$this->expectExceptionCode(400);

		$pdoConnection = $this->createMock(PDOConnection::class);
		$mysql = MySQL::create($pdoConnection, Statement::create());
		$fakeConnection = new FakePDOConnection();
		$mysql->addConnection($fakeConnection);
	}

	#[Test]
	public function changeConnectionException(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage("Connection: 'fake' does not exist.");
		$this->expectExceptionCode(400);

		$pdoConnection = $this->createMock(PDOConnection::class);
		$mysql = MySQL::create($pdoConnection, Statement::create());
		$mysql->dispatch(Action::CHANGE_CONNECTION, 'fake');
	}

	#[Test]
	public function dispatchSelect(): void
	{
		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('fetchAll')->willReturn([['UserID' => '10', 'Name' => 'John']]);

		$pdoConnection = $this->createMock(PDOConnection::class);
		$pdoConnection->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($pdoConnection, Statement::create());
		$mysql->dispatch(Action::SELECT, new DTO(collections: ['User'], fields: ['Name'], formatting: [Format::ARRAY]));
	}

	#[Test]
	public function dispatchUpdate(): void
	{
		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('rowCount')->willReturn(1);

		$pdoConnection = $this->createMock(PDOConnection::class);
		$pdoConnection->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($pdoConnection, Statement::create());
		$mysql->dispatch(Action::UPDATE, new DTO(collections: ['User'], fields: ['Name']));
	}

	#[Test]
	public function dispatchInsert(): void
	{
		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);

		$pdoConnection = $this->createMock(PDOConnection::class);
		$pdoConnection->expects($this->once())->method('prepare')->willReturn($pdoStatement);
		$pdoConnection->expects($this->once())->method('lastInsertId')->willReturn('1');

		$mysql = MySQL::create($pdoConnection, Statement::create());
		$mysql->dispatch(Action::INSERT, new DTO(collections: ['User'], fields: ['Name']));
	}

	#[Test]
	public function dispatchDelete(): void
	{
		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('rowCount')->willReturn(1);

		$pdoConnection = $this->createMock(PDOConnection::class);
		$pdoConnection->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($pdoConnection, Statement::create());
		$mysql->dispatch(Action::DELETE, new DTO(collections: ['User'], fields: ['Name']));
	}

	#[Test]
	public function dispatchExecute(): void
	{
		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->expects($this->once())->method('execute')->willReturn(true);
		$pdoStatement->expects($this->once())->method('fetchAll')->willReturn([['UserID' => '10', 'Name' => 'John']]);

		$pdoConnection = $this->createMock(PDOConnection::class);
		$pdoConnection->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($pdoConnection, Statement::create());
		$mysql->dispatch(Action::EXECUTE, ['query', ['param1', 'param2']]);
	}

	#[Test]
	public function dispatchQuery(): void
	{
		$this->expectNotToPerformAssertions();
		$pdoConnection = $this->createMock(PDOConnection::class);
		$mysql = MySQL::create($pdoConnection, Statement::create());
		$mysql->dispatch(Action::QUERY, [['User']]);
	}

	#[Test]
	public function dispatchStartTransaction(): void
	{
		$pdoConnection = $this->createMock(PDOConnection::class);
		$pdoConnection->expects($this->once())->method('beginTransaction')->willReturn(true);

		$mysql = MySQL::create($pdoConnection, Statement::create());
		$mysql->dispatch(Action::START_TRANSACTION, null);
	}

	#[Test]
	public function dispatchEndTransaction(): void
	{
		$pdoConnection = $this->createMock(PDOConnection::class);
		$pdoConnection->expects($this->once())->method('commit')->willReturn(true);

		$mysql = MySQL::create($pdoConnection, Statement::create());
		$mysql->dispatch(Action::END_TRANSACTION, null);
	}

	#[Test]
	public function dispatchRevertTransaction(): void
	{
		$pdoConnection = $this->createMock(PDOConnection::class);
		$pdoConnection->expects($this->once())->method('rollBack')->willReturn(true);

		$mysql = MySQL::create($pdoConnection, Statement::create());
		$mysql->dispatch(Action::REVERT_TRANSACTION, null);
	}

	#[Test]
	public function dispatchChangeConnection(): void
	{
		$pdoConnection = $this->createMock(PDOConnection::class);
		$pdoConnection->expects($this->once())->method('name')->willReturn(1);

		$mysql = MySQL::create($pdoConnection, Statement::create());
		$mysql->dispatch(Action::CHANGE_CONNECTION, 1);
	}

	#[Test]
	public function executeFailedToPrepareStatement(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Failed to prepare statement');
		$this->expectExceptionCode(500);

		$connection = $this->createMock(PDOConnection::class);
		$connection->expects($this->once())->method('prepare')->willReturn(false);
		$mysql = MySQL::create($connection, Statement::create());
		$mysql->dispatch(Action::EXECUTE, ['Select * FROM User', null]);
	}

	#[Test]
	public function executeFailedToExecuteStatement(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Failed to execute statement');
		$this->expectExceptionCode(500);

		$pdoStatement = $this->createMock(\PDOStatement::class);
		$pdoStatement->method('execute')->willReturn(false);

		$connection = $this->createMock(PDOConnection::class);
		$connection->expects($this->once())->method('prepare')->willReturn($pdoStatement);

		$mysql = MySQL::create($connection, Statement::create());
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

		$mysql = MySQL::create($connection, Statement::create());
		$query = $mysql->dispatch(Action::EXECUTE, [$sql, $params]);
		$this->assertEquals($expected, $query);
	}
}
