<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\Language\SQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Query\LogicalOperator;
use Projom\Storage\Database\Query\Operator;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Filter;

class FilterTest extends TestCase
{
	public static function createProvider(): array
	{
		return [
			[
				[['Name', Operator::EQ, 'John', LogicalOperator::AND]],
				['filter_name_1' => 'John'],
				'`Name` = :filter_name_1'
			],
			[
				[
					['Updated', Operator::LT, '2024-01-01 00:00:00', LogicalOperator::AND],
					['Deleted', Operator::LTE, '2024-01-01 00:00:00', LogicalOperator::AND],
					['Created', Operator::GTE, '2024-01-01 00:00:00', LogicalOperator::OR]
				],
				[
					'filter_updated_1' => '2024-01-01 00:00:00',
					'filter_deleted_2' => '2024-01-01 00:00:00',
					'filter_created_3' => '2024-01-01 00:00:00'
				],
				'`Updated` < :filter_updated_1 AND `Deleted` <= :filter_deleted_2 OR `Created` >= :filter_created_3'
			],
			[
				[['DeletedAt', Operator::IS_NULL, null, LogicalOperator::AND]],
				[],
				'`DeletedAt` IS NULL'
			],
			[
				[['UserID', Operator::IS_NOT_NULL, null, LogicalOperator::AND]],
				[],
				'`UserID` IS NOT NULL'
			],
			[
				[['UserID', Operator::IN, [1, 2, 3], LogicalOperator::AND]],
				['filter_userid_1_1' => 1, 'filter_userid_1_2' => 2, 'filter_userid_1_3' => 3],
				'`UserID` IN ( :filter_userid_1_1, :filter_userid_1_2, :filter_userid_1_3 )'
			],
			[
				[['UserRole.Name', Operator::EQ, 'leader', LogicalOperator::AND]],
				['filter_userrole_name_1' => 'leader'],
				'`UserRole`.`Name` = :filter_userrole_name_1'
			]
		];
	}

	#[Test]
	#[DataProvider('createProvider')]
	public function create(array $filters, array $expectedParams, string $expectedFilter): void
	{
		$filter = Filter::create($filters);
		$this->assertFalse($filter->empty());
		$this->assertEquals($expectedParams, $filter->params());
		$this->assertEquals($expectedFilter, "$filter");
		$this->assertEquals($filters, $filter->queryFilters());
	}

	#[Test]
	public function operator_not_found(): void
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

	#[Test]
	public function empty(): void
	{
		$filter = Filter::create([]);
		$this->assertTrue($filter->empty());
	}
}
