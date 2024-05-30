<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\MySQL\Order;
use Projom\Storage\Database\Sorts;

class OrderTest extends TestCase
{
	public static function create_test_provider(): array
	{
		return [
			[
				[
					['Name', Sorts::ASC]
				],
				'`Name` ASC'
			],
			[
				[
					['Name', Sorts::ASC,], 
					['Age', Sorts::DESC]
				],
				'`Name` ASC, `Age` DESC'
			],
			[
				[
					['UserID', Sorts::DESC], 
					['Email', Sorts::DESC]
				],
				'`UserID` DESC, `Email` DESC',
			]
		];
	}

	#[DataProvider('create_test_provider')]
	public function test_create(array $sortFields, string $expected)
	{
		$sort = Order::create($sortFields);
		$this->assertEquals($expected, "$sort");
		$this->assertFalse($sort->empty());
	}

	public static function merge_test_provider(): array
	{
		return [
			[
				[
					['Name', Sorts::ASC], 
					['Age', Sorts::DESC]
				],
				[ 
					['UserID', Sorts::DESC], 
					['Email', Sorts::DESC]
				],
				'`Name` ASC, `Age` DESC, `UserID` DESC, `Email` DESC'
			]
		];
	}

	#[DataProvider('merge_test_provider')]
	public function test_merge(array $sortFields1, array $sortFields2, string $expected)
	{
		$sort_1 = Order::create($sortFields1);
		$sort_2 = Order::create($sortFields2);
		$sort_1->merge($sort_2);
		$this->assertEquals($expected, "$sort_1");
		$this->assertFalse($sort_1->empty());
	}
}
