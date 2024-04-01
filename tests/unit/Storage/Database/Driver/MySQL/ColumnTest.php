<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Projom\Storage\Database\Driver\MySQL\Column;

class ColumnTest extends TestCase
{
	public function test_create(): void
	{
		$fields = ['field1', 'field2'];
		$column = Column::create($fields);

		$this->assertInstanceOf(Column::class, $column);
		$this->assertEquals($fields, $column->raw());
		$this->assertEquals('`field1`,`field2`', $column->get());
		$this->assertEquals('field1_field2', $column->joined('_'));
	}

	public function test_to_string(): void
	{
		$fields = ['field1', 'field2'];
		$column = new Column($fields);

		$this->assertEquals('`field1`,`field2`', "$column");
	}
}
