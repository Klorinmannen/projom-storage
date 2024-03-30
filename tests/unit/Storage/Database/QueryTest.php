<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\Filter;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Collection;
use Projom\Storage\Database\Query\Operators;

class QueryTest extends TestCase
{
	public function test_select(): void
	{
		$driver = $this->createMock(DriverInterface::class);
		$collection = 'users';
		$field = Field::create('name');
		$filter = Filter::create(['age' => 18], Operators::GT);

		$driver->expects($this->once())
			->method('select')
			->with(
				$this->equalTo(Collection::create($collection)),
				$this->equalTo($field),
				$this->equalTo($filter)
			)
			->willReturn(['John', 'Jane']);

		$query = new Query($driver, $collection);
		$result = $query->select($field, $filter);

		$this->assertEquals(['John', 'Jane'], $result);
	}

	public function test_fetch(): void
	{
		$driver = $this->createMock(DriverInterface::class);
		$collection = 'users';
		$field = 'name';
		$value = 'John';
		$operator = Operators::EQ;

		$driver->expects($this->once())
			->method('select')
			->with(
				$this->equalTo(Collection::create($collection)),
				$this->equalTo(Field::create($field)),
				$this->equalTo(Filter::create([$field => $value], $operator))
			)
			->willReturn(['John']);

		$query = new Query($driver, $collection);
		$result = $query->fetch($field, $value, $operator);

		$this->assertEquals(['John'], $result);
	}
}
