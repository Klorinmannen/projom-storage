<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\MySQL\Insert;
use Projom\Storage\Database\Query\Insert as QueryInsert;

class InsertTest extends TestCase
{
	public static function create_test_provider(): array
	{
		return [
			[
				[
					['User'],
					['Name' => 'John', 'Age' => 25]
				],
				[
					'INSERT INTO `User` (`Name`, `Age`) VALUES (?, ?)',
					['John', 25]
				]
			]
		];
	}

	#[DataProvider('create_test_provider')]
	public function test_create(array $parameters, array $expected): void
	{
		$queryInsert = new QueryInsert(...$parameters);
		$insert = Insert::create($queryInsert);
		$this->assertEquals($expected, $insert->query());
	}
}
