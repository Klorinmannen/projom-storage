<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL\Component;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\SQL\Component\Join;
use Projom\Storage\SQL\Util\Join as UtilJoin;

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
					['User.UserID', UtilJoin::INNER, 'Log.UserID'],
				],
				'INNER JOIN `Log` ON `User`.`UserID` = `Log`.`UserID`',
			],
			3 => [
				[
					['User.UserID = Log.UserID', UtilJoin::STRAIGHT, null],
				],
				'STRAIGHT JOIN `Log` ON `User`.`UserID` = `Log`.`UserID`'
			],
			4 => [
				[
					['User.UserID', UtilJoin::LEFT, 'UserRole.UserID'],
					['UserRole.UserRoleID = UserAccess.UserRoleID', UtilJoin::LEFT, null]
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
