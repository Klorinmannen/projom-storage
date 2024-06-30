<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\Language\SQL;

use PHPUnit\Framework\TestCase;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Column;

class ColumnTest extends TestCase
{
	public function test_create(): void
	{
		$fields = ['field1', 'field2'];
		$column = Column::create($fields);
		
		$this->assertEquals('`field1`, `field2`', "$column");
		$this->assertEquals('field1_field2', $column->joined('_'));
		$this->assertFalse($column->empty());
	}

	public function test_create_empty(): void
	{
		$fields = [];
		$column = Column::create($fields);
		
		$this->assertEquals('', "$column");
		$this->assertEquals('', $column->joined('_'));
		$this->assertTrue($column->empty());
	}
}
