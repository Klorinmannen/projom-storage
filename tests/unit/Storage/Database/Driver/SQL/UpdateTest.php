<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\SQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\SQL\Update;
use Projom\Storage\Database\Query\LogicalOperator;
use Projom\Storage\Database\Query\Operator;
use Projom\Storage\Database\Query\QueryObject;

class UpdateTest extends TestCase
{
	public static function create_test_provider(): array
	{
		return [
			[
				new QueryObject(
					collections: ['User'],
					fieldsWithValues: ['Name' => 'John'],
					filters: [['UserID', Operator::EQ, 10, LogicalOperator::AND]]
				),
				[
					'UPDATE `User` SET `Name` = :set_name_1 WHERE `UserID` = :filter_userid_1',
					['set_name_1' => 'John', 'filter_userid_1' => 10]
				]
			],
			[
				new QueryObject(
					collections: ['User'],
					fieldsWithValues: ['Name' => 'John']
				),
				[
					'UPDATE `User` SET `Name` = :set_name_1',
					['set_name_1' => 'John']
				]
			],
		];
	}

	#[DataProvider('create_test_provider')]
	public function test_create(QueryObject $queryObject, array $expected): void
	{
		$update = Update::create($queryObject);
		$this->assertEquals($expected, $update->query());
	}
}