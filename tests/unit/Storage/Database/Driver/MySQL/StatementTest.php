<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\MySQL\Column;
use Projom\Storage\Database\Driver\MySQL\Filter;
use Projom\Storage\Database\Driver\MySQL\Statement;
use Projom\Storage\Database\Driver\MySQL\Table;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\LogicalOperators;
use Projom\Storage\Database\Query\Operators;
use Projom\Storage\Database\Query\Value;

class StatementTest extends TestCase
{
	public static function select_test_provider(): array
	{
		return [
			'Default filter' => [
				'User',
				['Name'],
				[
					[
						Field::create('Name'),
						Operators::EQ,
						Value::create('John'),
						LogicalOperators::AND
					]
				],
				[
					'query' => 'SELECT `Name` FROM `User` WHERE `Name` = :filter_name_1',
					'params' => ['filter_name_1' => 'John']
				]
			],
			'Null filter' => [
				'User',
				['Name'],
				[
					[
						Field::create('Name'),
						Operators::IS_NULL,
						Value::create(null),
						LogicalOperators::AND
					]
				],
				[
					'query' => 'SELECT `Name` FROM `User` WHERE `Name` IS NULL',
					'params' => null
				]
			],
			'IN filter' => [
				'User',
				['*'],
				[
					[
						Field::create('Age'),
						Operators::IN,
						Value::create([12, 23, 45]),
						LogicalOperators::AND
					]
				],
				[
					'query' => 'SELECT * FROM `User` WHERE `Age` IN ( :filter_age_1_1, :filter_age_1_2, :filter_age_1_3 )',
					'params' => ['filter_age_1_1' => 12, 'filter_age_1_2' => 23, 'filter_age_1_3' => 45]
				]
			]
		];
	}

	#[DataProvider('select_test_provider')]
	public function test_select(string $table, array $columns, array $filter, array $expected): void
	{
		$table = new Table($table);
		$column = new Column($columns);
		$filter = new Filter($filter);

		[$query, $params] = Statement::select($table, $column, $filter);

		$this->assertEquals($expected['query'], $query);
		$this->assertEquals($expected['params'], $params);
	}
}
