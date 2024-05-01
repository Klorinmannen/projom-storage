<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\LogicalOperators;
use Projom\Storage\Database\Operators;
use Projom\Storage\Database\Driver\MySQL\Filter;

class FilterTest extends TestCase
{
	public function test_create(): void
	{
		$filter = Filter::create(['Name' => 'John'], Operators::EQ, LogicalOperators::AND);
		$filter2 = Filter::create(['Age' => 18], Operators::GT, LogicalOperators::AND);
		$filter->merge($filter2);

		$this->assertInstanceOf(Filter::class, $filter);
	}

	public function test_to_tring(): void
	{
		$filter = Filter::create(['Name' => 'John'], Operators::EQ, LogicalOperators::AND);
		$filter2 = Filter::create(['Age' => 18], Operators::GT, LogicalOperators::AND);
		$filter->merge($filter2);
		$filter->parse();

		$expected = '`Name` = :filter_name_1 AND `Age` > :filter_age_2';
		$this->assertEquals($expected, "$filter");
	}

	public function test_get(): void
	{
		$filter = Filter::create(['Name' => 'John'], Operators::EQ, LogicalOperators::AND);
		$filter2 = Filter::create(['Age' => 18], Operators::GT, LogicalOperators::AND);
		$filter->merge($filter2);
		$filter->parse();

		$expected = [
			['`Name` = :filter_name_1', 'AND `Age` > :filter_age_2'],
			[['filter_name_1' => 'John'], ['filter_age_2' => 18]],
		];
		$this->assertEquals($expected, $filter->get());
	}

	public function test_empty(): void
	{
		$filter = Filter::create([]);
		$filter->parse();

		$this->assertTrue($filter->empty());
	}

	public function test_params(): void
	{
		$filter = Filter::create(['Name' => 'John'], Operators::EQ, LogicalOperators::AND);
		$filter2 = Filter::create(['Age' => 18], Operators::GT, LogicalOperators::AND);
		$filter->merge($filter2);
		$filter->parse();

		$expected = ['filter_name_1' => 'John', 'filter_age_2' => 18];
		$this->assertEquals($expected, $filter->params());
	}

	public function test_filters(): void
	{
		$filter = Filter::create(['Name' => 'John'], Operators::EQ, LogicalOperators::AND);
		$filter2 = Filter::create(['Age' => 18], Operators::GT, LogicalOperators::AND);
		$filter->merge($filter2);
		$filter->parse();

		$expected = '`Name` = :filter_name_1 AND `Age` > :filter_age_2';
		$this->assertEquals($expected, $filter->filters());
	}
}
