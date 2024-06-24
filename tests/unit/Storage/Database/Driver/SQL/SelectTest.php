<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\SQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\SQL\Select;
use Projom\Storage\Database\LogicalOperators;
use Projom\Storage\Database\Operators;
use Projom\Storage\Database\Query\QueryObject;
use Projom\Storage\Database\Sorts;

class SelectTest extends TestCase
{
	public static function create_test_provider(): array
	{
		return [
			[
				new QueryObject(
					collections: ['User'],
					fields: ['UserID', 'Name'],
					filters: [['UserID', Operators::EQ, 10, LogicalOperators::AND]],
					sorts: [['UserID', Sorts::ASC], ['Name', Sorts::DESC]],
					limit: 10
				),
				[
					'SELECT `UserID`, `Name` FROM `User` WHERE `UserID` = :filter_userid_1 ORDER BY `UserID` ASC, `Name` DESC LIMIT 10',
					['filter_userid_1' => 10]
				]
			],
			[
				new QueryObject(
					collections: ['User'],
					fields: ['*'],
					sorts: [['UserID', Sorts::ASC], ['Name', Sorts::DESC]]
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

	#[DataProvider('create_test_provider')]
	public function test_create(QueryObject $queryObject, array $expected): void
	{
		$select = Select::create($queryObject);
		$this->assertEquals($expected, $select->query());
	}
}
