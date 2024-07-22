<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\Language\SQL\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\Driver\Language\SQL\Query\Insert;
use Projom\Storage\Database\Query\QueryObject;

class InsertTest extends TestCase
{
	public static function create_test_provider(): array
	{
		return [
			[
				new QueryObject(
					collections: ['User'],
					fieldsWithValues: [['Name' => 'John', 'Age' => 25]]
				),
				[
					'INSERT INTO `User` (`Name`, `Age`) VALUES (?, ?)',
					['John', 25]
				]
			],
			[
				new QueryObject(
					collections: ['User'],
					fieldsWithValues: [['Name' => 'John', 'Age' => 25], ['Name' => 'Jane', 'Age' => 30]]
				),
				[
					'INSERT INTO `User` (`Name`, `Age`) VALUES (?, ?), (?, ?)',
					['John', 25, 'Jane', 30]
				]
			]
		];
	}

	#[DataProvider('create_test_provider')]
	public function test_create(QueryObject $queryObject, array $expected): void
	{
		$insert = Insert::create($queryObject);
		$this->assertEquals($expected, $insert->query());
	}
}
