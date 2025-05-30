<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL\Statement;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\SQL\Statement\DTO;
use Projom\Storage\SQL\Statement\Delete;
use Projom\Storage\SQL\Util\Filter;
use Projom\Storage\SQL\Util\Join;
use Projom\Storage\SQL\Util\LogicalOperator;

class DeleteTest extends TestCase
{
	public static function create_provider(): array
	{
		return [
			[
				new DTO(
					collections: ['User'],
					joins: [['User.UserID = UserRole.UserID', Join::INNER, null]],
					filters: [
						[
							Filter::list(['UserRole.Role' => 'leader']),
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
				new DTO(
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
	public function create(DTO $queryObject, array $expected): void
	{
		$delete = Delete::create($queryObject);
		$this->assertEquals($expected, $delete->statement());
	}

	#[Test]
	public function stringable(): void
	{
		$delete = Delete::create(new DTO(collections: ['User']));
		$this->assertEquals('DELETE FROM `User`', (string) $delete);
	}
}
