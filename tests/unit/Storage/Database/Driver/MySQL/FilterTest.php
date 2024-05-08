<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\LogicalOperators;
use Projom\Storage\Database\Operators;
use Projom\Storage\Database\Driver\MySQL\Filter;

class FilterTest extends TestCase
{
	public static function filter(): array
	{
		return [
			[ 
				['Name', Operators::EQ, 'John', LogicalOperators::AND],
				['Age', Operators::GT, 18, LogicalOperators::AND]
			]
		];
	}	

	#[DataProvider('filter')]
	public function test_create(array $filter, array $filter2): void
	{
		$filter = Filter::create($filter);
		$filter2 = Filter::create($filter2);
		$filter->merge($filter2);

		$this->assertInstanceOf(Filter::class, $filter);
	}

	#[DataProvider('filter')]
	public function test_to_tring(array $filter, array $filter2): void
	{
		$filter = Filter::create($filter);
		$filter2 = Filter::create($filter2);
		$filter->merge($filter2);
		$filter->parse();

		$expected = '`Name` = :filter_name_1 AND `Age` > :filter_age_2';
		$this->assertEquals($expected, "$filter");
	}

	#[DataProvider('filter')]
	public function test_get(array $filter, array $filter2): void
	{
		$filter = Filter::create($filter);
		$filter2 = Filter::create($filter2);
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

	#[DataProvider('filter')]
	public function test_params(array $filter, array $filter2): void
	{
		$filter = Filter::create($filter);
		$filter2 = Filter::create($filter2);
		$filter->merge($filter2);
		$filter->parse();

		$expected = ['filter_name_1' => 'John', 'filter_age_2' => 18];
		$this->assertEquals($expected, $filter->params());
	}

	#[DataProvider('filter')]
	public function test_filters(array $filter, array $filter2): void
	{
		$filter = Filter::create($filter);
		$filter2 = Filter::create($filter2);
		$filter->merge($filter2);
		$filter->parse();

		$expected = '`Name` = :filter_name_1 AND `Age` > :filter_age_2';
		$this->assertEquals($expected, $filter->filters());
	}
}
