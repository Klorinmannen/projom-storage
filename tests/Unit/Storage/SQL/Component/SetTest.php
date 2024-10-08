<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL\Component;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\SQL\Component\Set;

class SetTest extends TestCase
{
	public static function createProvider(): array
	{
		return [
			[
				[[]],
				'',
				'',
				[],
				[],
				[]
			],
			[
				[['Name' => 'John', 'Age' => 25]],
				'`Name` = :set_name_1, `Age` = :set_age_2',
				'`Name`, `Age`',
				['?, ?'],
				[
					'set_name_1' => 'John',
					'set_age_2' => '25'
				],
				['John', 25]
			],
			[
				[['User.Name' => 'John', 'User.Age' => 25], ['User.Name' => 'Jane', 'User.Age' => 30]],
				'`User`.`Name` = :set_user_name_1, `User`.`Age` = :set_user_age_2, `User`.`Name` = :set_user_name_3, `User`.`Age` = :set_user_age_4',
				'`User`.`Name`, `User`.`Age`',
				['?, ?', '?, ?'],
				[
					'set_user_name_1' => 'John',
					'set_user_age_2' => '25',
					'set_user_name_3' => 'Jane',
					'set_user_age_4' => '30'
				],
				['John', 25, 'Jane', 30]
			]
		];
	}

	#[Test]
	#[DataProvider('createProvider')]
	public function create(
		array $fields,
		string $expectedSets,
		string $expectedPositionalFields,
		array $expectedPositionalParams,
		array $expectedParams,
		array $expectedPositionalParamValues
	): void {
		
		$set = Set::create($fields);

		$this->assertEquals($expectedSets, "$set");
		$this->assertEquals($expectedPositionalFields, $set->positionalFields());
		$this->assertEquals($expectedPositionalParams, $set->positionalParams());
		$this->assertEquals($expectedParams, $set->params());
		$this->assertEquals($expectedPositionalParamValues, $set->positionalParamValues());
	}

	#[Test]
	public function empty(): void
	{
		$set = Set::create([]);
		$this->assertTrue($set->empty());
	}
}
