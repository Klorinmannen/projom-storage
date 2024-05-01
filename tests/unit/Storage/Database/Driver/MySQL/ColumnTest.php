<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\TestCase;
use Projom\Storage\Database\Driver\MySQL\Column;
use Projom\Storage\Database\Query\Field;

class ColumnTest extends TestCase
{
	public function test_create(): void
	{
		$fields = ['field1', 'field2'];
		$column = Column::create($fields);
		$column->parse();
		
		$this->assertInstanceOf(Field::class, $column);
		$this->assertEquals('`field1`, `field2`', $column->get());
		$this->assertEquals('field1_field2', $column->joined('_'));
	}
}
