<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\Language\SQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\Driver\Language\SQL\Column;

class ColumnTest extends TestCase
{
	public static function createProvider(): array
	{
		return [
			[
				[],
				''
			],
			[
				['field1', 'field2'],
				'`field1`, `field2`'
			],
			[
				['Collection.Field', 'Collection.OtherField'],
				'`Collection`.`Field`, `Collection`.`OtherField`'				
			],
			[
				[ 'COUNT(*)', 'AVG(Collection.Field)', 'SUM(Collection.OtherField)', 'Username' ],
				'COUNT(*), AVG(`Collection`.`Field`), SUM(`Collection`.`OtherField`), `Username`'
			],
			[
				['Field1 as Alias1'],
				'`Field1` AS Alias1'
			],
			[
				['COUNT(*) AS Total'],
				'COUNT(*) AS Total'
			],
			[
				['SUM(Collection.Field) as Total'],
				'SUM(`Collection`.`Field`) AS Total'
			],
			[
				['Table.Field'],
				'`Table`.`Field`'
			],
			[
				['Field AS Alias'],
				'`Field` AS Alias'
			],
			[
				[ 'COUNT(*) as Total', 'AVG(Collection.Field) AS div', 'SUM(Collection.OtherField) As summ', 'Username' ],
				'COUNT(*) AS Total, AVG(`Collection`.`Field`) AS div, SUM(`Collection`.`OtherField`) AS summ, `Username`'
			],
			[
				['COUNTS(Field) AS Alias'],
				''
			],
		];
	}

	#[Test]
	#[DataProvider('createProvider')]
	public function create(array $fields, string $expected): void
	{
		$column = Column::create($fields);
		$result = "$column";
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function empty(): void
	{
		$fields = [];
		$column = Column::create($fields);
		$result = $column->empty();
		$this->assertTrue($result);

		$fields = ['field1', 'field2'];
		$column = Column::create($fields);
		$result = $column->empty();
		$this->assertFalse($result);
	}

	#[Test]
	public function fields(): void
	{
		$fields = ['field1', 'field2'];
		$column = Column::create($fields);
		
		$result = $column->fields();
		$expected = $fields;
		$this->assertEquals($expected, $result);
	}
}
