<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\SQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Query\LogicalOperator;
use Projom\Storage\Database\Query\Operator;
use Projom\Storage\Database\Driver\SQL\Filter;
use ReflectionClass;

class FilterTest extends TestCase
{
	public static function createProvider(): array
	{
		return [
			[
				[['Name', Operator::EQ, 'John', LogicalOperator::AND]],
				['filter_name_1' => 'John']
			],
			[
				[['Age', Operator::GT, 18, LogicalOperator::AND]],
				['filter_age_1' => 18]
			],
			[
				[['Created', Operator::GTE, '2024-01-01 00:00:00', LogicalOperator::AND]],
				['filter_created_1' => '2024-01-01 00:00:00']
			],
			[
				[['Updated', Operator::LT, '2024-01-01 00:00:00', LogicalOperator::AND]],
				['filter_updated_1' => '2024-01-01 00:00:00']
			],
			[
				[['Deleted', Operator::LTE, '2024-01-01 00:00:00', LogicalOperator::AND]],
				['filter_deleted_1' => '2024-01-01 00:00:00']
			],
			[
				[['DeletedAt', Operator::IS_NULL, null, LogicalOperator::AND]],
				[]
			],
			[
				[['UserID', Operator::IS_NOT_NULL, null, LogicalOperator::AND]],
				[]
			],
			[
				[['UserID', Operator::IN, [1, 2, 3], LogicalOperator::AND]],
				['filter_userid_1_1' => 1, 'filter_userid_1_2' => 2, 'filter_userid_1_3' => 3]
			]
		];
	}

	#[Test]
	public function create_operator_not_found(): void
	{
		// This test will throw an exception if a case is not handled.

		$this->expectNotToPerformAssertions();

		$value = 'John';
		$cases = Operator::cases();
		foreach ($cases as $case) {

			$value = match ($case) {
				Operator::IN, Operator::NOT_IN => [$value],
				default => $value
			};
		}

		Filter::create([['Name', $case, $value, LogicalOperator::AND]]);
	}

	#[Test]
	#[DataProvider('createProvider')]
	public function create(array $filters, array $expected): void
	{
		$filter = Filter::create($filters);
		$this->assertFalse($filter->empty());
		$this->assertEquals($expected, $filter->params());
		$this->assertEquals($filters, $filter->queryFilters());
	}

	public static function mergeProvider(): array
	{
		return [
			[
				[['Name', Operator::EQ, 'John', LogicalOperator::AND]],
				[['Age', Operator::EQ, 18, LogicalOperator::AND]],
				['filter_name_1' => 'John', 'filter_age_2' => 18]
			],
			[
				[['UserID', Operator::EQ, 1, LogicalOperator::AND]],
				[['Username', Operator::EQ, 'john@example.com', LogicalOperator::OR]],
				['filter_userid_1' => 1, 'filter_username_2' => 'john@example.com']
			]
		];
	}

	#[Test]
	#[DataProvider('mergeProvider')]
	public function merge(array $filter1, array $filter2, array $expected): void
	{
		$filter = Filter::create($filter1);
		$filterOther = Filter::create($filter2);
		$filter->merge($filterOther);

		$this->assertFalse($filter->empty());
		$this->assertEquals($expected, $filter->params());
		$this->assertEquals([...$filter1, ...$filter2], $filter->queryFilters());
	}

	public function test_empty(): void
	{
		$filter = Filter::create([]);
		$this->assertTrue($filter->empty());
	}
}
