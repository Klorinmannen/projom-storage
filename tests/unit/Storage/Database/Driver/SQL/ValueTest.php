<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\SQL;

use PHPUnit\Framework\TestCase;
use Projom\Storage\Database\Driver\SQL\Value;
use Projom\Storage\Database\Values;

class ValueTest extends TestCase
{

	public function testCreate(): void
	{
		$value = Value::create('Hello');
		$this->assertEquals('Hello', $value->get());
		$this->assertEquals(Values::STRING, $value->getType());

		$value = Value::create(true);
		$this->assertEquals(true, $value->get());
		$this->assertEquals(Values::BOOL, $value->getType());

		$value = Value::create(123);
		$this->assertEquals(123, $value->get());
		$this->assertEquals(Values::NUMERIC, $value->getType());

		$value = Value::create(null);
		$this->assertEquals(null, $value->get());
		$this->assertEquals(Values::NULL, $value->getType());

		$value = Value::create(['apple', 'banana', 'orange']);
		$this->assertEquals(['apple', 'banana', 'orange'], $value->get());
		$this->assertEquals(Values::ARRAY, $value->getType());

		$value = Value::create(3.14);
		$this->assertEquals(3.14, $value->get());
		$this->assertEquals(Values::NUMERIC, $value->getType());
	}

	public function testIsNull(): void
	{
		$value = Value::create(null);
		$this->assertTrue($value->isNull());

		$value = Value::create('Hello');
		$this->assertFalse($value->isNull());
	}

	public function testEmpty(): void
	{
		$value = Value::create(fn() => 'Hello');
		$this->assertTrue($value->empty());

		$value = Value::create('Hello');
		$this->assertFalse($value->empty());
	}
}
