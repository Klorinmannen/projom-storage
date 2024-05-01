<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\MySQL;
use Projom\Storage\Database\Query\Collection;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Driver\MySQL\Filter;
use Projom\Storage\Database\Drivers;
use Projom\Storage\Database\Source\PDOSource;
use Projom\Storage\Database\Query;

class MySQLTest extends TestCase
{
	public function test_type(): void
	{
		$source = $this->createMock(PDOSource::class);
		$mysql = new MySQL($source);

		$this->assertEquals(Drivers::MySQL, $mysql->type());
	}

	public function test_select(): void
	{
		$query = 'SELECT * FROM `User`';
		$params = null;

		$records = [
			['Name' => 'John', 'Age' => 18],
			['Name' => 'Jane', 'Age' => 21]
		];

		$source = $this->createMock(PDOSource::class);
		$source->expects($this->once())
			->method('execute')
			->with($this->equalTo($query), $this->equalTo($params))
			->willReturn($records);

		$mysql = new MySQL($source);

		$collection = $this->createMock(Collection::class);
		$collection->method('get')
			->willReturn('User');

		$field = $this->createMock(Field::class);
		$field->method('get')
			->willReturn(['*']);

		$filter = $this->createMock(Filter::class);
		$filter->method('get')
			->willReturn([]);

		$result = $mysql->select($collection, $field, $filter);

		$this->assertEquals($records, $result);
	}

	public function test_query(): void
	{
		$source = $this->createMock(PDOSource::class);
		$mysql = new MySQL($source);

		$query = $mysql->Query('table');

		$this->assertInstanceOf(Query::class, $query);
	}

	public function test_execute(): void
	{
		$sql = 'INSERT INTO `User` (`Name`) VALUES (?)';
		$params = ['John'];

		$source = $this->createMock(PDOSource::class);
		$source->expects($this->once())
			->method('execute')
			->with($this->equalTo($sql), $this->equalTo($params))
			->willReturn([]);

		$mysql = new MySQL($source);
		$result = $mysql->execute($sql, $params);

		$this->assertEquals([], $result);
	}
}
