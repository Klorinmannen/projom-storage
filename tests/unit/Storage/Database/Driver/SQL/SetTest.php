<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\SQL;

use PHPUnit\Framework\TestCase;
use Projom\Storage\Database\Driver\SQL\Set;

class SetTest extends TestCase
{
	public function test_create(): void
	{
		$fields = [ 'Name' => 'John', 'Age' => 25 ];
		$set = Set::create($fields);
		
		$sets = [
			'`Name` = :set_name_1',
			'`Age` = :set_age_2'		
		];

		$fields = [
			'set_name_1' => '`Name`',
			'set_age_2' => '`Age`'
		];

		$params = [
			'set_name_1' => 'John',
			'set_age_2' => 25
		];

		$this->assertEquals($sets, $set->sets());
		$this->assertEquals(implode(', ', $sets), "$set");
		$this->assertEquals($fields, $set->fields());
		$this->assertEquals($params, $set->params());
		$this->assertFalse($set->empty());
		$this->assertEquals(implode(', ', $fields), $set->positionalFields());
		$this->assertEquals('?, ?', $set->positionalParams());
		$this->assertEquals(['John', 25], $set->positionalParamValues());
	}
}
