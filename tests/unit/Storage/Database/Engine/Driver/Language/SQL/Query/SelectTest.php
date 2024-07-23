<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\Language\SQL\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\Driver\Language\SQL\Query\Select;
use Projom\Storage\Database\Query\LogicalOperator;
use Projom\Storage\Database\Query\Operator;
use Projom\Storage\Database\Query\QueryObject;
use Projom\Storage\Database\Query\Sort;
use Projom\Storage\Database\Query\Join;

class SelectTest extends TestCase
{
	public static function create_test_provider(): array
	{
		return [
			[
				new QueryObject(
					collections: ['User'],
					fields: ['UserID', 'Name'],
					joins: [['User.UserID', Join::INNER, 'Log.UserID']],
					filters: [
						[
							[['UserID', Operator::EQ, 10], ['Log.RequestType', Operator::EQ, 'GET']],
							LogicalOperator::AND
						]
					],
					sorts: [['UserID', Sort::ASC], ['Name', Sort::DESC]],
					limit: 10,
					groups: ['Name']
				),
				[
					'SELECT `UserID`, `Name` FROM `User` INNER JOIN `Log` ON `User`.`UserID` = `Log`.`UserID`' .
						' WHERE ( `UserID` = :filter_userid_1 AND `Log`.`RequestType` = :filter_log_requesttype_2 )' .
						' GROUP BY `Name` ORDER BY `UserID` ASC, `Name` DESC LIMIT 10',
					[
						'filter_userid_1' => 10,
						'filter_log_requesttype_2' => 'GET'
					]
				]
			],
			[
				new QueryObject(
					collections: ['User'],
					fields: ['*'],
					sorts: [['UserID', Sort::ASC], ['Name', Sort::DESC]]
				),
				[
					'SELECT * FROM `User` ORDER BY `UserID` ASC, `Name` DESC',
					null
				]
			],
			[
				new QueryObject(
					collections: ['User'],
					fields: ['*'],
					limit: 10
				),
				[
					'SELECT * FROM `User` LIMIT 10',
					null
				]
			],
			[
				new QueryObject(
					collections: ['User'],
					fields: ['*']
				),
				[
					'SELECT * FROM `User`',
					null
				]
			],
		];
	}

	#[Test]
	#[DataProvider('create_test_provider')]
	public function create(QueryObject $queryObject, array $expected): void
	{
		$select = Select::create($queryObject);
		$this->assertEquals($expected, $select->query());
	}
}
