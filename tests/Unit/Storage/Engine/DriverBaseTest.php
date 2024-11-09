<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Engine;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Query\Action;
use Projom\Storage\Engine\Driver\ConnectionInterface;
use Projom\Storage\Engine\DriverBase;
use Projom\Storage\Query\Format;
use Projom\Storage\Query\RecordInterface;

class DriverStub extends DriverBase
{
	public function addConnection(ConnectionInterface $connection): void {}
	public function changeConnection(int|string $name): void {}

	public function dispatch(Action $action, mixed $args): mixed
	{
		return null;
	}

	public function testProcessRecords($records, $format): null|array
	{
		return $this->processRecords($records, $format);
	}
}

class User implements RecordInterface
{
	public string $name;
	public int $age;

	public function __construct(string $name, int $age)
	{
		$this->name = $name;
		$this->age = $age;
	}

	public static function createFromRecord(array $record): User
	{
		$user = new User($record['Name'], $record['Age']);
		return $user;
	}
}

class DriverBaseTest extends TestCase
{
	#[Test]
	public function formatRecords(): void
	{
		$driver = new DriverStub();

		$records = [
			[
				'Name' => 'John',
				'Age' => 25
			]
		];
		$actual = $driver->testProcessRecords($records, [Format::ARRAY, null]);
		$expected = $records;
		$this->assertEquals($expected, $actual);

		$actual = $driver->testProcessRecords($records, [Format::STD_CLASS, null]);
		$expected = [(object) $records[0]];
		$this->assertEquals($expected, $actual);

		$actual = $driver->testProcessRecords($records, [Format::CUSTOM_OBJECT, User::class]);
		$expected = [User::createFromRecord($records[0])];
		$this->assertEquals($expected, $actual);
	}

	public static function formatRecordsExceptionProvider(): array
	{
		return [
			[
				Format::CUSTOM_OBJECT,
				null,
				'Class name not provided.',
				400
			],
			[
				Format::CUSTOM_OBJECT,
				'NonExistentClass',
				'Class: NonExistentClass does not exist.',
				400
			],
			[
				Format::CUSTOM_OBJECT,
				\stdClass::class,
				'Class: stdClass must implement RecordInterface.',
				400
			]
		];
	}

	#[Test]
	#[DataProvider('formatRecordsExceptionProvider')]
	public function formatRecordsException(Format $format, mixed $className, string $message, int $code): void
	{
		$driver = new DriverStub();

		$records = [
			[
				'Name' => 'John',
				'Age' => 25
			]
		];

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage($message);
		$this->expectExceptionCode($code);
		$driver->testProcessRecords($records, [$format, $className]);
	}
}
