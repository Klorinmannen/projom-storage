<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\SQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Query\LogicalOperator;
use Projom\Storage\Database\Query\Operator;
use Projom\Storage\Database\Driver\SQL\Filter;

class FilterTest extends TestCase
{
	public static function filter(): array
	{
		return [
			[ 
				[ ['Name', Operator::EQ, 'John', LogicalOperator::AND] ],
				[ ['Age', Operator::GT, 18, LogicalOperator::AND] ]
			]
		];
	}	

	#[DataProvider('filter')]
	public function test_create(array $filter1, array $filter2): void
	{
		$filter = Filter::create($filter1);
		$this->assertFalse($filter->empty());
		$this->assertEquals(['filter_name_1' => 'John'], $filter->params());
		$this->assertEquals($filter1, $filter->queryFilters());

		$filterOther = Filter::create($filter2);
		$this->assertFalse($filterOther->empty());
		$this->assertEquals(['filter_age_1' => 18], $filterOther->params());
		$this->assertEquals($filter2, $filterOther->queryFilters());

		$filter->merge($filterOther);
		$expected = '`Name` = :filter_name_1 AND `Age` > :filter_age_2';
		$this->assertEquals($expected, "$filter");
	}

	#[DataProvider('filter')]
	public function test_merge(array $filter1, array $filter2): void
	{
		$filter = Filter::create($filter1);
		$filterOther = Filter::create($filter2);
		$filter->merge($filterOther);

		$this->assertFalse($filter->empty());
		$this->assertEquals(['filter_name_1' => 'John', 'filter_age_2' => 18], $filter->params());
		$this->assertEquals([...$filter1, ...$filter2], $filter->queryFilters());
	}

	public function test_empty(): void
	{
		$filter = Filter::create([]);
		$this->assertTrue($filter->empty());
	}
}
