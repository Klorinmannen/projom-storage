<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\SQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\SQL\Insert;
use Projom\Storage\Database\Query\QueryObject;

class InsertTest extends TestCase
{
	public static function create_test_provider(): array
	{
		return [
			[
				new QueryObject(
					collections: ['User'],
					fieldsWithValues: ['Name' => 'John', 'Age' => 25]
				),
				[
					'INSERT INTO `User` (`Name`, `Age`) VALUES (?, ?)',
					['John', 25]
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
