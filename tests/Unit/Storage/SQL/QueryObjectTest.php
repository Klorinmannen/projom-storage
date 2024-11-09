<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Projom\Storage\SQL\QueryObject;

class QueryObjectTest extends TestCase
{
	public static function constructProvider(): array
	{
		return [
			'empty' => [
				[
					'collections' => [],
					'fields' => [],
					'fieldsWithValues' => [],
					'joins' => [],
					'filters' => [],
					'sorts' => [],
					'groups' => [],
					'limit' => null,
					'offset' => null,
					'formatting' => [],
				],
				'expected' => '{"collections":[],"fields":[],"fieldsWithValues":[],'
					. '"joins":[],"filters":[],"sorts":[],"groups":[],'
					. '"limit":null,"offset":null,"formatting":[]}',
			],
			'filled' => [
				[
					'collections' => ['table'],
					'fields' => ['field'],
					'fieldsWithValues' => ['field' => 'value'],
					'joins' => [['TableA.Field', 'INNER JOIN', 'TableB.Field']],
					'filters' => [['Field', 'Operator', 'Value', 'AND']],
					'sorts' => [['sort', 'ASC']],
					'groups' => ['FieldA', 'FieldB'],
					'limit' => 10,
					'offset' => 5,
					'formatting' => ['ARRAY', null]
				],
				'expected' => '{"collections":["table"],'
					. '"fields":["field"],' 
					. '"fieldsWithValues":{"field":"value"},' 
					. '"joins":[["TableA.Field","INNER JOIN","TableB.Field"]],' 
					. '"filters":[["Field","Operator","Value","AND"]],'
					. '"sorts":[["sort","ASC"]],'
					. '"groups":["FieldA","FieldB"],'
					. '"limit":10,"offset":5,'
					. '"formatting":["ARRAY",null]}',
			]
		];
	}

	#[Test]
	#[DataProvider('constructProvider')]
	public function construct(array $config, string $expected): void 
	{
		$queryObject = new QueryObject(
			$config['collections'],
			$config['fields'],
			$config['fieldsWithValues'],
			$config['joins'],
			$config['filters'],
			$config['sorts'],
			$config['groups'],
			$config['limit'],
			$config['offset'],
			$config['formatting']
		);

		$this->assertEquals($expected, (string) $queryObject);
	}
}
