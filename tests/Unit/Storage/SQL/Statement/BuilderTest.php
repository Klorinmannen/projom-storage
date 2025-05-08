<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL\Statement;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Psr\Log\NullLogger;

use Projom\Storage\Engine\Driver\MySQL as MySQLDriver;
use Projom\Storage\Query\Format;
use Projom\Storage\SQL\Statement\Builder;
use Projom\Storage\SQL\Util\Join;
use Projom\Storage\SQL\Util\LogicalOperator;
use Projom\Storage\SQL\Util\Operator;

class BuilderTest extends TestCase
{
	#[Test]
	public function create(): void
	{
		$driver = $this->createMock(MySQLDriver::class);
		$query = Builder::create(driver: $driver, collections: ['User'], options: [], logger: new NullLogger());
		$this->assertInstanceOf(Builder::class, $query);
	}

	#[Test]
	public function formatAs(): void
	{
		$driver = $this->createMock(MySQLDriver::class);
		$query = Builder::create($driver, ['User']);

		$query = $query->formatAs(Format::STD_CLASS);
		$this->assertInstanceOf(Builder::class, $query);
	}

	public static function fetchSelectGetProvider(): array
	{
		return [
			[
				[
					0 => [
						'Name' => 'John',
						'Age' => 25
					]
				]
			],
			[
				null
			]
		];
	}

	#[Test]
	#[DataProvider('fetchSelectGetProvider')]
	public function fetchSelectGet(null|array $expected): void
	{
		$driver = $this->createMock(MySQLDriver::class);
		$driver->expects($this->atLeastOnce())->method('dispatch')->willReturn($expected);
		$query = Builder::create($driver, ['User']);

		$result = $query->fetch('Name', 'John');
		$this->assertEquals($expected, $result);

		$result = $query->select('Name', 'Age');
		$this->assertEquals($expected, $result);

		$result = $query->get('Name', 'Age');
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function updateModify(): void
	{
		$expected = 1;
		$driver = $this->createMock(MySQLDriver::class);
		$driver->expects($this->atLeastOnce())->method('dispatch')->willReturn($expected);
		$query = Builder::create($driver, ['User']);

		$result = $query->update(['Name' => 'Jane', 'Age' => 21]);
		$this->assertEquals($expected, $result);

		$result = $query->modify(['Name' => 'Jane', 'Age' => 21]);
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function insertAdd(): void
	{
		$expected = 1;
		$driver = $this->createMock(MySQLDriver::class);
		$driver->expects($this->atLeastOnce())->method('dispatch')->willReturn($expected);
		$query = Builder::create($driver, ['User']);

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
	public function deleteDestroy(): void
	{
		$expected = 2;
		$driver = $this->createMock(MySQLDriver::class);
		$driver->expects($this->atLeastOnce())->method('dispatch')->willReturn($expected);
		$query = Builder::create($driver, ['User']);

		$result = $query->delete();
		$this->assertEquals($expected, $result);

		$result = $query->destroy();
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function joinOn(): void
	{
		$query = Builder::create(null, ['User']);
		$query = $query->joinOn('User.UserID = UserRole.UserID', Join::INNER);
		$this->assertInstanceOf(Builder::class, $query);
	}

	#[Test]
	public function filter(): void
	{
		$query = Builder::create(null, ['User']);
		$query = $query->filter(['Name', Operator::IN, ['John', 'Jane']], LogicalOperator::AND);
		$this->assertInstanceOf(Builder::class, $query);
	}

	#[Test]
	public function filterList(): void
	{
		$query = Builder::create(null, ['User']);
		$query = $query->filterList([['Name', Operator::IN, ['John', 'Jane']]], LogicalOperator::AND);
		$this->assertInstanceOf(Builder::class, $query);
	}

	#[Test]
	public function filterOnFields(): void
	{
		$query = Builder::create(null, ['User']);
		$query = $query->filterOnFields(['Name' => ['John', 'Jane']], Operator::IN, LogicalOperator::AND);
		$this->assertInstanceOf(Builder::class, $query);
	}

	#[Test]
	public function filterOn(): void
	{
		$query = Builder::create(null, ['User']);
		$query = $query->filterOn('Name', ['John', 'Jane'], Operator::IN, LogicalOperator::AND);
		$this->assertInstanceOf(Builder::class, $query);
	}

	#[Test]
	public function groupOn(): void
	{
		$query = Builder::create(null, ['User']);
		$query = $query->groupOn('Username')->groupOn('Name', 'Age')->groupOn('Lastname, Age');
		$this->assertInstanceOf(Builder::class, $query);
	}

	#[Test]
	public function sortOn(): void
	{
		$query = Builder::create(null, ['User']);
		$query = $query->sortOn(['Name' => 'ASC'])->sortOn(['Age' => 'DESC']);
		$this->assertInstanceOf(Builder::class, $query);
	}

	#[Test]
	public function limit(): void
	{
		$query = Builder::create(null, ['User']);
		$query = $query->limit(10);
		$this->assertInstanceOf(Builder::class, $query);
	}

	#[Test]
	public function offset(): void
	{
		$query = Builder::create(null, ['User']);
		$query = $query->offset(5);
		$this->assertInstanceOf(Builder::class, $query);
	}
}
