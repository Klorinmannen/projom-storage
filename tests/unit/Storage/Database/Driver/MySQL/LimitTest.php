<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\TestCase;
use Projom\Storage\Database\Driver\MySQL\Limit;

class LimitTest extends TestCase
{
	public function test_create()
	{
		$limits = 10;
		$limit = Limit::create($limits);

		$this->assertEquals($limits, $limit->get());
		$this->assertEquals("LIMIT $limits", "$limit");
		$this->assertFalse($limit->empty());

		$limits = '';
		$limit = Limit::create($limits);
		$this->assertTrue($limit->empty());
	}
}