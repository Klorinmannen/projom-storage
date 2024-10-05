<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Util;

class UtilTest extends TestCase
{
	#[Test]
	public function join(): void
	{
		$expected = '123,456';
		$result = Util::join(['123', '456']);
		$this->assertEquals($expected, $result);

		$expected = '123_456';
		$result = Util::join(['123', '456'], '_');
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function split(): void
	{
		$string = '123,456';
		$result = Util::split($string);
		$expected = ['123', '456'];
		$this->assertEquals($expected, $result);

		$string = '123_456';
		$result = Util::split($string, '_');
		$expected = ['123', '456'];
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function flatten(): void
	{
		$list = [[1, 2], [3, 4]];
		$result = Util::flatten($list);
		$expected = [1, 2, 3, 4];
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function merge(): void
	{
		$list1 = [1, 2];
		$list2 = [3, 4];
		$result = Util::merge($list1, $list2);
		$expected = [1, 2, 3, 4];
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function removeEmpty(): void
	{
		$list = [1, '', 2, null, 3];
		$result = Util::removeEmpty($list);
		$expected = [
			0 => 1,
			2 => 2,
			4 => 3
		];
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function match(): void
	{
		$pattern = '/\d{3}/';
		$subject = '123456';
		$result = Util::match($pattern, $subject);
		$expected = ['123'];
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function is_int(): void
	{
		$subject = 123;
		$result = Util::is_int($subject);
		$this->assertTrue($result);

		$subject = 123.456;
		$result = Util::is_int($subject);
		$this->assertFalse($result);

		$subject = '123';
		$result = Util::is_int($subject);
		$this->assertTrue($result);

		$subject = '123.456';
		$result = Util::is_int($subject);
		$this->assertFalse($result);
	}
}
