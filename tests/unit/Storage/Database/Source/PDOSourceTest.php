<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Source;

use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Source\PDOSource;

use PDO;
use PDOStatement;
use Exception;

class PDOSourceTest extends TestCase
{
	public function test_create(): void
	{
		$pdo = $this->createMock(PDO::class);
		$source = PDOSource::create($pdo);

		$this->assertInstanceOf(PDOSource::class, $source);
	}

	public function test_execute(): void
	{
		$records = [['Name' => 'John', 'Age' => '27']];

		$statement = $this->createMock(PDOStatement::class);
		$statement->expects($this->once())->method('execute')->willReturn(true);
		$statement->expects($this->once())->method('fetchAll')->willReturn($records);

		$pdo = $this->createMock(PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($statement);

		$source = PDOSource::create($pdo);
		
		$sql = 'SELECT * FROM users';
		$source->execute($sql);

		$result = $source->fetchResult();
		$this->assertIsArray($result);
		$this->assertEquals($records, $result);
	}

	public function test_execute_failed_prepare(): void
	{
		$pdo = $this->createMock(PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn(false);
		$source = PDOSource::create($pdo);

		$this->expectException(Exception::class);
		$this->expectExceptionCode(500);

		$sql = 'SELECT * FROM users';
		$source->execute($sql);
	}

	public function test_execute_failed_statement_execute(): void
	{
		$statement = $this->createMock(PDOStatement::class);
		$statement->expects($this->once())->method('execute')->willReturn(false);

		$pdo = $this->createMock(PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($statement);
		$source = PDOSource::create($pdo);

		$this->expectException(Exception::class);
		$this->expectExceptionCode(500);

		$sql = 'SELECT * FROM users';
		$source->execute($sql);
	}

	public function test_get(): void
	{
		$pdo = $this->createMock(PDO::class);
		$source = PDOSource::create($pdo);

		$this->assertInstanceOf(PDO::class, $source->get());
	}

	public function test_fetch_result(): void
	{
		$records = [['Name' => 'John', 'Age' => '25']];

		$statement = $this->createMock(PDOStatement::class);
		$statement->expects($this->once())->method('execute')->willReturn(true);
		$statement->expects($this->once())->method('fetchAll')->willReturn($records);

		$pdo = $this->createMock(PDO::class);
		$pdo->expects($this->once())->method('prepare')->willReturn($statement);

		$source = PDOSource::create($pdo);
		$sql = 'SELECT * FROM users';
		$source->execute($sql);

		$result = $source->fetchResult();
		$this->assertIsArray($result);
		$this->assertEquals($records, $result);
	}
}
