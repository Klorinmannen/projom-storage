<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\MySQL\Table;

class TableTest extends TestCase
{
	public function test_create(): void
	{
		$table = Table::create('User');
		$this->assertInstanceOf(Table::class, $table);
	}

	public function test_to_string(): void
	{
		$table = Table::create('User');
		$this->assertEquals('`User`', "$table");
	}

	public function test_get(): void
	{
		$table = Table::create('User');
		$this->assertEquals('`User`', $table->get());
	}

}