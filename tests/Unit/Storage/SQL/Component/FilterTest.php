<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL\Component;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\SQL\Component\Filter;
use Projom\Storage\SQL\Util\Filter as QueryFilter;
use Projom\Storage\SQL\Util\LogicalOperator;
use Projom\Storage\SQL\Util\Operator;

class FilterTest extends TestCase
{
	public static function createProvider(): array
	{
		return [
			'Simple' => [
				[
					[
						QueryFilter::list(['Name' => 'John']),
						LogicalOperator::AND
					]
				],
				['filter_name_1' => 'John'],
				'( `Name` = :filter_name_1 )'
			],
			'Combine filters' => [
				[
					[
						QueryFilter::combine(
							QueryFilter::build('UpdatedAt', '2024-01-01 00:00:00', Operator::LT),
							QueryFilter::build('DeletedAt', '2024-01-01 00:00:00', Operator::LTE),
							QueryFilter::build('CreatedAt', '2024-01-01 00:00:00', Operator::GTE)
						),
						LogicalOperator::AND
					]
				],
				[
					'filter_updatedat_1' => '2024-01-01 00:00:00',
					'filter_deletedat_2' => '2024-01-01 00:00:00',
					'filter_createdat_3' => '2024-01-01 00:00:00'
				],
				'( `UpdatedAt` < :filter_updatedat_1' .
					' AND `DeletedAt` <= :filter_deletedat_2' .
					' AND `CreatedAt` >= :filter_createdat_3 )'
			],
			'IS_NULL' => [
				[
					[
						QueryFilter::list(['DeletedAt' => null], Operator::IS_NULL),
						LogicalOperator::AND
					]
				],
				[],
				'( `DeletedAt` IS NULL )'
			],
			'IS_NOT_NULL' => [
				[
					[
						QueryFilter::list(['UserID' => null], Operator::IS_NOT_NULL),
						LogicalOperator::AND
					]
				],
				[],
				'( `UserID` IS NOT NULL )'
			],
			'IN' => [
				[
					[
						QueryFilter::list(['UserID' => [1, 2, 3]], Operator::IN),
						LogicalOperator::AND
					]
				],
				['filter_userid_1_1' => 1, 'filter_userid_1_2' => 2, 'filter_userid_1_3' => 3],
				'( `UserID` IN ( :filter_userid_1_1, :filter_userid_1_2, :filter_userid_1_3 ) )'
			],
			'Tesing "Table.Field"' => [
				[
					[
						QueryFilter::list(['UserRole.Role' => 'leader'], Operator::EQ),
						LogicalOperator::AND
					]
				],
				['filter_userrole_role_1' => 'leader'],
				'( `UserRole`.`Role` = :filter_userrole_role_1 )'
			],
			'Complex filter' => [
				[
					[
						QueryFilter::combine(
							QueryFilter::build('UpdatedAt', '2024-01-01 00:00:00', Operator::LT),
							QueryFilter::build('DeletedAt', '2024-01-01 00:00:00', Operator::LTE),
							QueryFilter::build('CreatedAt', '2024-01-01 00:00:00', Operator::GTE)
						),
						LogicalOperator::AND
					],
					[
						QueryFilter::list(['UserID' => [10, 20, 30]], Operator::IN),
						LogicalOperator::OR
					],
					[
						QueryFilter::list(
							['Password' => null, 'Username' => null, 'Name' => null],
							Operator::IS_NOT_NULL,
							LogicalOperator::OR
						),
						LogicalOperator::AND
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
					' OR ( `UserID` IN ( :filter_userid_4_1, :filter_userid_4_2, :filter_userid_4_3 ) )' .
					' AND ( `Password` IS NOT NULL OR `Username` IS NOT NULL OR `Name` IS NOT NULL ) )'
			],
			'Complex filter 2' => [
				[
					[
						QueryFilter::list([
							'UpdatedAt' => '2024-01-01 00:00:00',
							'DeletedAt' => '2024-01-01 00:00:00',
							'CreatedAt' => '2024-01-01 00:00:00'
						], Operator::LT),
						LogicalOperator::AND
					],
					[
						QueryFilter::combine(
							QueryFilter::build('UserID', [10, 20, 30], Operator::IN),
							QueryFilter::build('Username', null, Operator::IS_NOT_NULL)
						),
						LogicalOperator::OR
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
				'( ( `UpdatedAt` < :filter_updatedat_1 AND `DeletedAt` < :filter_deletedat_2 AND `CreatedAt` < :filter_createdat_3 )' .
					' OR ( `UserID` IN ( :filter_userid_4_1, :filter_userid_4_2, :filter_userid_4_3 ) AND `Username` IS NOT NULL ) )'
			],
			'LIKE / NOT LIKE' => [
				[
					[
						QueryFilter::combine(
							QueryFilter::build('Username', 'A__a', Operator::LIKE),
							QueryFilter::build('Username', 'J%', Operator::NOT_LIKE)
						),
						LogicalOperator::AND
					]
				],
				['filter_username_1' => 'A__a', 'filter_username_2' => 'J%'],
				'( `Username` LIKE :filter_username_1 AND `Username` NOT LIKE :filter_username_2 )'
			],
			'BETWEEN / NOT BETWEEN' => [
				[
					[
						QueryFilter::combine(
							QueryFilter::build('Age', [18, 30], Operator::BETWEEN),
							QueryFilter::build('Age', [22, 26], Operator::NOT_BETWEEN)
						),
						LogicalOperator::AND
					]
				],
				['filter_age_1_1' => 18, 'filter_age_1_2' => 30, 'filter_age_2_1' => 22, 'filter_age_2_2' => 26],
				'( `Age` BETWEEN :filter_age_1_1 AND :filter_age_1_2 AND `Age` NOT BETWEEN :filter_age_2_1 AND :filter_age_2_2 )'
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
				Operator::BETWEEN, Operator::NOT_BETWEEN => [$value, $value],
				default => $value
			};

			$filter = QueryFilter::list(['Name' => $value], $case);
			Filter::create([[$filter, LogicalOperator::AND]]);
		}
	}

	#[Test]
	public function empty(): void
	{
		$filter = Filter::create([]);
		$this->assertTrue($filter->empty());
	}
}
