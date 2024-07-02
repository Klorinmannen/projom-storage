<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\Language\SQL\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\Driver\Language\SQL\Query\Delete;
use Projom\Storage\Database\Query\Join;
use Projom\Storage\Database\Query\LogicalOperator;
use Projom\Storage\Database\Query\QueryObject;
use Projom\Storage\Database\Query\Operator;

class DeleteTest extends TestCase
{
	public static function create_test_provider(): array
	{
		return [
			[
				new QueryObject(
					collections: ['User'],
					joins: [[Join::INNER, 'UserRole.UserID = User.UserID', null]],
					filters: [['UserRole.Role', Operator::EQ, 'leader', LogicalOperator::AND]]
				),
				[
					'DELETE FROM `User`' .
					' INNER JOIN `UserRole` ON `User`.`UserID` = `UserRole`.`UserID`' .
					' WHERE `UserRole`.`Role` = :filter_userrole_role_1',
					['filter_userrole_role_1' => 'leader']
				]
			],
			[
				new QueryObject(
					collections: ['User']
				),
				[
					'DELETE FROM `User`',
					null
				]
			],
		];
	}

	#[DataProvider('create_test_provider')]
	public function test_create(QueryObject $queryObject, array $expected): void
	{
		$delete = Delete::create($queryObject);
		$this->assertEquals($expected, $delete->query());
	}
}
