<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\LogicalOperators;
use Projom\Storage\Database\Operators;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Value;
use Projom\Storage\Database\Driver\MySQL\Filter;

class FilterTest extends TestCase
{
	public function test_create(): void
	{
		$filters = [
			[Field::create('name'), Operators::EQ, Value::create('John'), LogicalOperators::AND],
			[Field::create('age'), Operators::GT, Value::create(18), LogicalOperators::AND],
		];

		$filter = Filter::create($filters);

		$this->assertInstanceOf(Filter::class, $filter);
	}

	public function test_to_tring(): void
	{
		$filters = [
			[Field::create('Name'), Operators::EQ, Value::create('John'), LogicalOperators::AND],
			[Field::create('Age'), Operators::GT, Value::create(18), LogicalOperators::AND],
		];

		$filter = Filter::create($filters);

		$expected = '`Name` = :filter_name_1 AND `Age` > :filter_age_2';
		$this->assertEquals($expected, "$filter");
	}

	public function test_raw(): void
	{
		$filters = [
			[Field::create('Name'), Operators::EQ, Value::create('John'), LogicalOperators::AND],
			[Field::create('Age'), Operators::GT, Value::create(18), LogicalOperators::AND],
		];

		$filter = Filter::create($filters);

		$expected = $filters;
		$this->assertEquals($expected, $filter->raw());
	}

	public function test_get(): void
	{
		$filters = [
			[Field::create('Name'), Operators::EQ, Value::create('John'), LogicalOperators::AND],
			[Field::create('Age'), Operators::GT, Value::create(18), LogicalOperators::AND],
		];

		$filter = Filter::create($filters);

		$expected = [
			['filter' => '`Name` = :filter_name_1', 'params' => ['filter_name_1' => 'John']],
			['filter' => '`Age` > :filter_age_2', 'params' => ['filter_age_2' => 18]],
		];
		$this->assertEquals($expected, $filter->get());
	}

	public function test_empty(): void
	{
		$filter = Filter::create([]);

		$this->assertTrue($filter->empty());
	}

	public function test_params(): void
	{
		$filters = [
			[Field::create('Name'), Operators::EQ, Value::create('John'), LogicalOperators::AND],
			[Field::create('Age'), Operators::GT, Value::create(18), LogicalOperators::AND],
		];

		$filter = Filter::create($filters);

		$expected = ['filter_name_1' => 'John', 'filter_age_2' => 18];
		$this->assertEquals($expected, $filter->params());
	}

	public function test_filters(): void
	{
		$filters = [
			[Field::create('Name'), Operators::EQ, Value::create('John'), LogicalOperators::AND],
			[Field::create('Age'), Operators::GT, Value::create(18), LogicalOperators::AND]
		];

		$filter = Filter::create($filters);

		$expected = '`Name` = :filter_name_1 AND `Age` > :filter_age_2';
		$this->assertEquals($expected, $filter->filters());
	}
}
