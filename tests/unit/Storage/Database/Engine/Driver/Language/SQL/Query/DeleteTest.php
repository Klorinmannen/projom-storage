<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\Language\SQL\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\Driver\Language\SQL\Query\Delete;
use Projom\Storage\Database\Query\Filter;
use Projom\Storage\Database\Query\Join;
use Projom\Storage\Database\Query\LogicalOperator;
use Projom\Storage\Database\Query\QueryObject;

class DeleteTest extends TestCase
{
	public static function create_provider(): array
	{
		return [
			[
				new QueryObject(
					collections: ['User'],
					joins: [['UserRole.UserID = User.UserID', Join::INNER, null]],
					filters: [
						[
							Filter::buildGroup(['UserRole.Role' => 'leader']),
							LogicalOperator::AND
						]
					]
				),
				[
					'DELETE FROM `User`' .
						' INNER JOIN `UserRole` ON `User`.`UserID` = `UserRole`.`UserID`' .
						' WHERE ( `UserRole`.`Role` = :filter_userrole_role_1 )',
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

	#[Test]
	#[DataProvider('create_provider')]
	public function create(QueryObject $queryObject, array $expected): void
	{
		$delete = Delete::create($queryObject);
		$this->assertEquals($expected, $delete->query());
	}
}
