<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\MySQL\Update;
use Projom\Storage\Database\LogicalOperators;
use Projom\Storage\Database\Operators;
use Projom\Storage\Database\Query\Update as QueryUpdate;

class UpdateTest extends TestCase
{
	public static function create_test_provider(): array
	{
		return [
			[
				[
					['User'],
					['Name' => 'John'],
					[['UserID', Operators::EQ, 10, LogicalOperators::AND]]
				],
				[
					'UPDATE `User` SET `Name` = :set_name_1 WHERE `UserID` = :filter_userid_1',
					['set_name_1' => 'John', 'filter_userid_1' => 10]
				]
			],
			[
				[
					['User'],
					['Name' => 'John'],
					[]
				],
				[
					'UPDATE `User` SET `Name` = :set_name_1',
					['set_name_1' => 'John']
				]
			],
		];
	}

	#[DataProvider('create_test_provider')]
	public function test_create(array $parameters, array $expected): void
	{
		$queryUpdate = new QueryUpdate(...$parameters);
		$update = Update::create($queryUpdate);
		$this->assertEquals($expected, $update->query());
	}
}