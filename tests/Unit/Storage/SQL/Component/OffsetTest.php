<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL\Component;

use PHPUnit\Framework\TestCase;

use Projom\Storage\SQL\Component\Offset;

class OffsetTest extends TestCase
{
	public function test_create()
	{
		$offset = 10;
		$actual = Offset::create($offset);
		$expected = $offset;

		$this->assertEquals($expected, "$actual");
		$this->assertFalse($actual->empty());
	}

	public function test_create_empty()
	{
		$offset = Offset::create(0);
		$this->assertTrue($offset->empty());
	}
}
