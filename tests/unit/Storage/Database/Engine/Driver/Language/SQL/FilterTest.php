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
				[
					['Name', Operator::EQ, 'John', LogicalOperator::AND]
				],
				['filter_name_1' => 'John'],
				'`Name` = :filter_name_1'
			],
			[
				[
					['Updated', Operator::LT, '2024-01-01 00:00:00', LogicalOperator::AND],
					['Deleted', Operator::LTE, '2024-01-01 00:00:00', LogicalOperator::OR],
					['Created', Operator::GTE, '2024-01-01 00:00:00', LogicalOperator::AND]
				],
				[
					'filter_updated_1' => '2024-01-01 00:00:00',
					'filter_deleted_2' => '2024-01-01 00:00:00',
					'filter_created_3' => '2024-01-01 00:00:00'
				],
				'( `Updated` < :filter_updated_1 AND `Deleted` <= :filter_deleted_2 OR `Created` >= :filter_created_3 )'
			],
			[
				[
					['DeletedAt', Operator::IS_NULL, null, LogicalOperator::AND]
				],
				[],
				'`DeletedAt` IS NULL'
			],
			[
				[
					['UserID', Operator::IS_NOT_NULL, null, LogicalOperator::AND]
				],
				[],
				'`UserID` IS NOT NULL'
			],
			[
				[
					['UserID', Operator::IN, [1, 2, 3], LogicalOperator::AND]
				],
				['filter_userid_1_1' => 1, 'filter_userid_1_2' => 2, 'filter_userid_1_3' => 3],
				'`UserID` IN ( :filter_userid_1_1, :filter_userid_1_2, :filter_userid_1_3 )'
			],
			[
				[
					['UserRole.Name', Operator::EQ, 'leader', LogicalOperator::AND]
				],
				['filter_userrole_name_1' => 'leader'],
				'`UserRole`.`Name` = :filter_userrole_name_1'
			],
			[
				[
					[
						['UpdatedAt', Operator::LT, '2024-01-01 00:00:00', LogicalOperator::AND],
						['DeletedAt', Operator::LTE, '2024-01-01 00:00:00', LogicalOperator::AND],
						['CreatedAt', Operator::GTE, '2024-01-01 00:00:00', LogicalOperator::AND],
					],
					['UserID', Operator::IN, [10, 20, 30], LogicalOperator::OR],
					[
						['Password', Operator::IS_NOT_NULL, null, LogicalOperator::AND],
						['Username', Operator::IS_NOT_NULL, null, LogicalOperator::AND],
					]
				],
				[
					'filter_updatedat_1' => '2024-01-01 00:00:00',
					'filter_deletedat_2' => '2024-01-01 00:00:00',
					'filter_createdat_3' => '2024-01-01 00:00:00',
					'filter_userid_4_1' => 10,
					'filter_userid_4_2' => 20,
					'filter_userid_4_3' => 30
				],
				'( ( `UpdatedAt` < :filter_updatedat_1 AND `DeletedAt` <= :filter_deletedat_2 AND `CreatedAt` >= :filter_createdat_3 )' .
					' AND `UserID` IN ( :filter_userid_4_1, :filter_userid_4_2, :filter_userid_4_3 ) OR ( `Password` IS NOT NULL AND `Username` IS NOT NULL ) )'
			],
			[
				[
					[
						['UpdatedAt', Operator::LT, '2024-01-01 00:00:00', LogicalOperator::AND],
						['DeletedAt', Operator::LTE, '2024-01-01 00:00:00', LogicalOperator::AND],
						['CreatedAt', Operator::GTE, '2024-01-01 00:00:00', LogicalOperator::OR],
					],
					[
						['UserID', Operator::IN, [10, 20, 30], LogicalOperator::AND],
						['Username', Operator::IS_NOT_NULL, null, LogicalOperator::AND],
					]
				],
				[
					'filter_updatedat_1' => '2024-01-01 00:00:00',
					'filter_deletedat_2' => '2024-01-01 00:00:00',
					'filter_createdat_3' => '2024-01-01 00:00:00',
					'filter_userid_4_1' => 10,
					'filter_userid_4_2' => 20,
					'filter_userid_4_3' => 30
				],
				'( ( `UpdatedAt` < :filter_updatedat_1 AND `DeletedAt` <= :filter_deletedat_2 AND `CreatedAt` >= :filter_createdat_3 )' .
					' OR ( `UserID` IN ( :filter_userid_4_1, :filter_userid_4_2, :filter_userid_4_3 ) AND `Username` IS NOT NULL ) )'
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

			Filter::create([['Name', $case, $value, LogicalOperator::AND]]);
		}
	}

	#[Test]
	public function empty(): void
	{
		$filter = Filter::create([]);
		$this->assertTrue($filter->empty());
	}
}
