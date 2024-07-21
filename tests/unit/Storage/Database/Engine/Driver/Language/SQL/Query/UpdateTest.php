<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\Language\SQL\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\Driver\Language\SQL\Query\Update;
use Projom\Storage\Database\Query\Join;
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
					fieldsWithValues: [['Name' => 'John']],
					joins: [['UserRole.UserID = User.UserID', Join::INNER, null]],
					filters: [
						['UserRole.Role', Operator::EQ, 'leader', LogicalOperator::AND]
					]
				),
				[
					'UPDATE `User` SET `Name` = :set_name_1' .
					' INNER JOIN `UserRole` ON `User`.`UserID` = `UserRole`.`UserID`' .
					' WHERE `UserRole`.`Role` = :filter_userrole_role_1',
					['set_name_1' => 'John', 'filter_userrole_role_1' => 'leader']
				]
			],
			[
				new QueryObject(
					collections: ['User'],
					fieldsWithValues: [['Name' => 'John']]
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