<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Source;

use PHPUnit\Framework\Attributes\DataProvider;
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

	public function test_get(): void
	{
		$pdo = $this->createMock(PDO::class);
		$source = PDOSource::create($pdo);

		$this->assertInstanceOf(PDO::class, $source->get());
	}

	public function test_execute(): void
	{
		$pdo = $this->createMock(PDO::class);
		$sql = 'SELECT * FROM users';
		$records = [['Name' => 'John', 'Age' => '27']];

		$statement = $this->createMock(PDOStatement::class);
		$statement->expects($this->once())
			->method('execute')
			->with($this->equalTo(null))
			->willReturn(true);

		$statement->expects($this->once())
			->method('fetchAll')
			->willReturn($records);

		$pdo->expects($this->once())
			->method('prepare')
			->with($this->equalTo($sql))
			->willReturn($statement);

		$source = PDOSource::create($pdo);
		$result = $source->execute($sql);
		$this->assertIsArray($result);
		$this->assertEquals($records, $result);
	}

	public function test_failed_prepare_exception(): void
	{
		$pdostmnt = $this->createMock(PDOStatement::class);
		$sql = 'SELECT * FROM users';

		// Failed to prepare PDO query
		$pdostmnt->expects($this->once())
			->method('execute')
			->with($this->equalTo(null))
			->willReturn(false);

		$pdo = $this->createMock(PDO::class);
		$pdo->expects($this->once())
			->method('prepare')
			->with($this->equalTo($sql))
			->willReturn($pdostmnt);

		$source = PDOSource::create($pdo);
		$this->expectException(Exception::class);
		$this->expectExceptionCode(500);		
		$source->execute($sql);
	}

	public function test_failed_execute_exception(): void
	{
		$pdo = $this->createMock(PDO::class);
		$sql = 'SELECT * FROM users';

		$statement = $this->createMock(PDOStatement::class);
		$statement->expects($this->once())
			->method('execute')
			->with($this->equalTo(null))
			->willReturn(false);

		$pdo->expects($this->once())
			->method('prepare')
			->with($this->equalTo($sql))
			->willReturn($statement);

		$source = PDOSource::create($pdo);
		$this->expectException(Exception::class);
		$this->expectExceptionCode(500);
		$source->execute($sql);
	}
}
