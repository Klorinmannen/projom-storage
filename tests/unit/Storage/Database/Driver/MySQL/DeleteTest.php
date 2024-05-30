<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\MySQL\Delete;
use Projom\Storage\Database\LogicalOperators;
use Projom\Storage\Database\Query\Delete as QueryDelete;
use Projom\Storage\Database\Operators;

class DeleteTest extends TestCase
{
	public static function create_test_provider(): array
	{
		return [
			[
				[
					['User'],
					[['UserID', Operators::EQ, 10, LogicalOperators::AND]]
				],
				[
					'DELETE FROM `User` WHERE `UserID` = :filter_userid_1',
					['filter_userid_1' => 10]
				]
			],
			[
				[
					['User'],
					[]
				],
				[
					'DELETE FROM `User`',
					null
				]
			],
		];
	}

	#[DataProvider('create_test_provider')]
	public function test_create(array $parameters, array $expected): void
	{
		$queryDelete = new QueryDelete(...$parameters);
		$delete = Delete::create($queryDelete);
		$this->assertEquals($expected, $delete->query());
	}
}
