<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\TestCase;
use Projom\Storage\Database\Driver\MySQL\Sort;
use Projom\Storage\Database\Sorts;

class SortTest extends TestCase
{
	public function test_create()
	{
		$sortFields = ['Name' => Sorts::ASC, 'Age' => Sorts::DESC];
		$sort = Sort::create($sortFields);

		$expected = [
			'`Name` ASC',
			'`Age` DESC'
		];

		$this->assertEquals($expected, $sort->parsed());
		$this->assertEquals('ORDER BY `Name` ASC, `Age` DESC', $sort->string());
		$this->assertEquals('ORDER BY `Name` ASC, `Age` DESC', "$sort");
		$this->assertFalse($sort->empty());
	}

	public function test_merge()
	{
		$sortFields = ['Name' => Sorts::ASC, 'Age' => Sorts::DESC];
		$sort_1 = Sort::create($sortFields);

		$sortFields = [ 'UserID' => Sorts::DESC, 'Email' => Sorts::DESC];
		$sort_2 = Sort::create($sortFields);

		$sort_1->merge($sort_2);

		$expected = [
			'`Name` ASC',
			'`Age` DESC',
			'`UserID` DESC',
			'`Email` DESC'
		];

		$this->assertEquals($expected, $sort_1->parsed());
		$this->assertEquals('ORDER BY `Name` ASC, `Age` DESC, `UserID` DESC, `Email` DESC', $sort_1->string());
		$this->assertEquals('ORDER BY `Name` ASC, `Age` DESC, `UserID` DESC, `Email` DESC', "$sort_1");
		$this->assertFalse($sort_1->empty());
	}
}
