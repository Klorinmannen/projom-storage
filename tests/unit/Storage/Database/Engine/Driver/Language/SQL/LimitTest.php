<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\Language\SQL;

use PHPUnit\Framework\TestCase;
use Projom\Storage\Database\Engine\Driver\Language\SQL\Limit;

class LimitTest extends TestCase
{
	public function test_create_int()
	{
		$limits = 10;
		$limit = Limit::create($limits);

		$this->assertEquals($limits, "$limit");
		$this->assertFalse($limit->empty());
	}

	public function test_create_string()
	{
		$limits = '10';
		$limit = Limit::create($limits);

		$this->assertEquals($limits, "$limit");
		$this->assertFalse($limit->empty());
	}

	public function test_create_empty()
	{
		$limit = Limit::create(0);
		$this->assertTrue($limit->empty());

		$limit = Limit::create('');
		$this->assertTrue($limit->empty());
	}
}