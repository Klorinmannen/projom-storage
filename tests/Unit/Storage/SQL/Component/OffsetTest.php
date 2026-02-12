<?php

declare(strict_types=1);

namespace JRF\Tests\Unit\Storage\SQL\Component;

use PHPUnit\Framework\TestCase;

use JRF\Storage\Internal\Database\SQL\Component\Offset;

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
		$offset = Offset::create(null);
		$this->assertTrue($offset->empty());
	}
}
