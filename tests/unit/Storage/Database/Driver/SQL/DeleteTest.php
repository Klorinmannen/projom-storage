<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\SQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\SQL\Delete;
use Projom\Storage\Database\LogicalOperators;
use Projom\Storage\Database\Query\QueryObject;
use Projom\Storage\Database\Operators;

class DeleteTest extends TestCase
{
	public static function create_test_provider(): array
	{
		return [
			[
				new QueryObject(
					collections: ['User'],
					filters: [['UserID', Operators::EQ, 10, LogicalOperators::AND]]
				),
				[
					'DELETE FROM `User` WHERE `UserID` = :filter_userid_1',
					['filter_userid_1' => 10]
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
