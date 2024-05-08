<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\TestCase;
use Projom\Storage\Database\Driver\MySQL\Set;

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

		$this->assertEquals([ 'sets' => $sets, 'fields' => $fields, 'params' => $params ], $set->get());
		$this->assertEquals($sets, $set->sets());
		$this->assertEquals('`Name` = :set_name_1, `Age` = :set_age_2', $set->asString());
		$this->assertEquals($fields, $set->fields());
		$this->assertEquals('`Name`, `Age`' ,$set->fieldString());
		$this->assertEquals(['set_name_1', 'set_age_2'], $set->valueFields());
		$this->assertEquals($params, $set->params());
		$this->assertEquals('?, ?', $set->positionalString());
		$this->assertEquals(['John', 25], $set->positionalParams());
	}
}
