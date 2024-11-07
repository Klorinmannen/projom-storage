<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL\Statement;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\SQL\QueryObject;
use Projom\Storage\SQL\Statement\Update;
use Projom\Storage\SQL\Util\Filter;
use Projom\Storage\SQL\Util\Join;
use Projom\Storage\SQL\Util\LogicalOperator;

class UpdateTest extends TestCase
{
	public static function createProvider(): array
	{
		return [
			[
				new QueryObject(
					collections: ['User'],
					fieldsWithValues: [['Name' => 'John']],
					joins: [['User.UserID = UserRole.UserID', Join::INNER, null]],
					filters: [
						[
							Filter::list(['UserRole.Role' => 'leader']),
							LogicalOperator::AND
						]
					]
				),
				[
					'UPDATE `User` SET `Name` = :set_name_1' .
						' INNER JOIN `UserRole` ON `User`.`UserID` = `UserRole`.`UserID`' .
						' WHERE ( `UserRole`.`Role` = :filter_userrole_role_1 )',
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

	#[Test]
	#[DataProvider('createProvider')]
	public function create(QueryObject $queryObject, array $expected): void
	{
		$update = Update::create($queryObject);
		$this->assertEquals($expected, $update->statement());
	}

	#[Test]
	public function stringable(): void
	{
		$queryObject = new QueryObject(
			collections: ['User'],
			fieldsWithValues: [['User.Name' => 'John']],
			joins: [['User.UserID = UserRole.UserID', Join::INNER, null]],
			filters: [
				[
					Filter::list(['UserRole.Role' => 'leader']),
					LogicalOperator::AND
				]
			]
		);
		$update = Update::create($queryObject);
		$this->assertEquals('UPDATE `User` SET `User`.`Name` = :set_user_name_1' .
			' INNER JOIN `UserRole` ON `User`.`UserID` = `UserRole`.`UserID`' .
			' WHERE ( `UserRole`.`Role` = :filter_userrole_role_1 )', (string) $update);
	}
}
