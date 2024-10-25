<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL\Component;

use PHPUnit\Framework\TestCase;

use Projom\Storage\SQL\Component\Limit;

class LimitTest extends TestCase
{
	public function test_create_int()
	{
		$limits = 10;
		$limit = Limit::create($limits);

		$this->assertEquals($limits, "$limit");
		$this->assertFalse($limit->empty());
	}

	public function test_create_empty()
	{
		$limit = Limit::create(0);
		$this->assertFalse($limit->empty());
	}
}