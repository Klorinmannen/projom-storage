<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\MySQL\Select;
use Projom\Storage\Database\LogicalOperators;
use Projom\Storage\Database\Operators;
use Projom\Storage\Database\Query\Select as QuerySelect;
use Projom\Storage\Database\Sorts;

class SelectTest extends TestCase
{
	public static function create_test_provider(): array
	{
		return [
			[
				[
					['User'],
					['UserID', 'Name'],
					[['UserID', Operators::EQ, 10, LogicalOperators::AND]],
					[['UserID', Sorts::ASC], ['Name', Sorts::DESC]],
					10,
				],
				[
					'SELECT `UserID`, `Name` FROM `User` WHERE `UserID` = :filter_userid_1 ORDER BY `UserID` ASC, `Name` DESC LIMIT 10',
					['filter_userid_1' => 10]
				]
			],
			[
				[
					['User'],
					['*'],
					[],
					[['UserID', Sorts::ASC], ['Name', Sorts::DESC]]
				],
				[
					'SELECT * FROM `User` ORDER BY `UserID` ASC, `Name` DESC',
					null
				]
			],
			[
				[
					['User'],
					['*'],
					[],
					[],
					10
				],
				[
					'SELECT * FROM `User` LIMIT 10',
					null
				]
			],			
			[
				[
					['User'],
					['*']
				],
				[
					'SELECT * FROM `User`',
					null
				]
			],
		];
	}

	#[DataProvider('create_test_provider')]
	public function test_create(array $parameters, array $expected): void
	{
		$querySelect = new QuerySelect(...$parameters);
		$select = Select::create($querySelect);
		$this->assertEquals($expected, $select->query());
	}
}
