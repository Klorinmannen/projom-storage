<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Engine\Driver\Language\SQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\Driver\Language\SQL\Join;
use Projom\Storage\Database\Query\Join as QueryJoin;

class JoinTest extends TestCase
{
	public static function createProvider(): array
	{
		return [
			1 => [
				[],
				''
			],
			2 => [
				[
					['User.UserID', QueryJoin::INNER, 'Log.UserID'],
				],
				'INNER JOIN `Log` ON `User`.`UserID` = `Log`.`UserID`',
			],
			3 => [
				[
					['User.UserID = Log.UserID', QueryJoin::STRAIGHT, null],
				],
				'STRAIGHT JOIN `Log` ON `User`.`UserID` = `Log`.`UserID`'
			],
			4 => [
				[
					['User.UserID', QueryJoin::LEFT, 'UserRole.UserID'],
					['UserRole.UserRoleID = UserAccess.UserRoleID', QueryJoin::LEFT, null]
				],
				'LEFT JOIN `UserRole` ON `User`.`UserID` = `UserRole`.`UserID`' .
				' LEFT JOIN `UserAccess` ON `UserRole`.`UserRoleID` = `UserAccess`.`UserRoleID`'
			]
		];
	}

	#[Test]
	#[DataProvider('createProvider')]
	public function create(array $joins, string $expected): void
	{
		$join = Join::create($joins);

		$this->assertEquals($expected, "$join");
	}

	#[Test]
	public function empty(): void
	{
		$join = Join::create([]);

		$this->assertTrue($join->empty());
	}
}
