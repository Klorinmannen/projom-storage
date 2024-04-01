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
use Projom\Storage\Database\Query\Operator;
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
						Operator::create(Operators::EQ),
						Value::create('John')
					]
				],
				[
					'query' => 'SELECT `Name` FROM `User` WHERE `Name` = :named_name_1',
					'params' => ['named_name_1' => 'John']
				]
			],
			'Null filter' => [
				'User',
				['Name'],
				[
					[
						Field::create('Name'),
						Operator::create(Operators::IS_NULL),
						Value::create(null)
					]
				],
				[
					'query' => 'SELECT `Name` FROM `User` WHERE `Name` IS NULL',
					'params' => []
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

		$statement = Statement::create($table, $column, $filter);

		[$query, $params] = $statement->select();

		$this->assertEquals($expected['query'], $query);
		$this->assertEquals($expected['params'], $params);
	}
}
