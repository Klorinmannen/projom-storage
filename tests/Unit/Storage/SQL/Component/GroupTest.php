<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL\Component;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\SQL\Component\Group;

class GroupTest extends TestCase
{

	public static function createProvider(): array
	{
		return [
			[
				[],
				''
			],
			[
				[['field1']],
				'`field1`'
			],			[
				[['field1', 'field2', 'field3']],
				'`field1`, `field2`, `field3`'
			],
			[
				[['field1, field2, field3']],
				'`field1`, `field2`, `field3`'
			],
			[
				[['User.Name', 'UserRole.Role']],
				'`User`.`Name`, `UserRole`.`Role`'
			],
			[
				[['User.Name, UserRole.Role']],
				'`User`.`Name`, `UserRole`.`Role`'
			]
		];
	}

	#[Test]
	#[DataProvider('createProvider')]
	public function create(array $fields, string $expected): void
	{
		$group = Group::create($fields);
		$this->assertEquals($expected, "$group");
	}

	#[Test]
	public function createEmpty(): void
	{
		$group = Group::create([[]]);
		$this->assertTrue($group->empty());
		$this->assertEquals('', "$group");
	}

	#[Test]
	public function createNoneEmpty(): void
	{
		$group = Group::create([['field1, field2']]);
		$this->assertFalse($group->empty());
	}
}