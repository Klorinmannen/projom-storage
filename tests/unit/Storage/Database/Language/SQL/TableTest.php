<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Language\SQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Language\SQL\Table;

class TableTest extends TestCase
{
	public static function create_test_provider(): array
	{
		return [
			[
				['User'], 
				'`User`'
			],
			[
				[ 'User', 'UserAccess'], 
				'`User`, `UserAccess`'
			],
		];
	}

	#[DataProvider('create_test_provider')]
	public function test_create(array $tables, string $expected): void
	{
		$table = Table::create($tables);
		$this->assertEquals($expected, "$table");
	}

	public static function empty_test_provider(): array
	{
		return [
			[
				['User'], 
				false
			],
			[
				[], 
				true
			]
		];
	}

	#[DataProvider('empty_test_provider')]
	public function test_empty(array $tables, bool $expected): void
	{
		$table = Table::create($tables);
		$this->assertEquals($expected, $table->empty());
	}
}