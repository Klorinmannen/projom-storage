<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Util;

class UtilTest extends TestCase
{
	public static function stringToListProvider(): array
	{
		return [
			['123', ',', ['123']],
			['123, 456', ',', ['123', '456']],
			[['123', '456'], ',', ['123', '456']],
			['123_456', '_', ['123', '456']],
			['123 456', ' ', ['123456']],
		];
	}

	#[Test]
	#[DataProvider('stringToListProvider')]
	public function stringToList(string|array $subject, string $delimeter, array $expected): void
	{
		$result = Util::stringToList($subject, $delimeter);
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function cleanString(): void
	{
		$expected = '123';
		$result = Util::cleanString(' 123 ');
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function cleanList(): void
	{
		$expected = ['123', '456'];
		$result = Util::cleanList([' 123 ', ' 456 ']);
		$this->assertEquals($expected, $result);
	}

	public static function cleanProvider(): array
	{
		return [
			['123', '123'],
			[[' 123 ', ' 456 '], ['123', '456']],
		];
	}

	#[Test]
	#[DataProvider('cleanProvider')]
	public function clean(string|array $subject, string|array $expected): void
	{
		$result = Util::clean($subject);
		$this->assertEquals($expected, $result);
	}

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
	public function isInt(): void
	{
		$subject = 123;
		$result = Util::isInt($subject);
		$this->assertTrue($result);

		$subject = 123.456;
		$result = Util::isInt($subject);
		$this->assertFalse($result);

		$subject = '123';
		$result = Util::isInt($subject);
		$this->assertTrue($result);

		$subject = '123.456';
		$result = Util::isInt($subject);
		$this->assertFalse($result);
	}

	#[Test]
	public function rekey(): void
	{
		$records = [
			['id' => 1, 'name' => 'John'],
			['id' => 2, 'name' => 'Jane'],
		];
		$result = Util::rekey($records, 'id');
		$expected = [
			1 => ['id' => 1, 'name' => 'John'],
			2 => ['id' => 2, 'name' => 'Jane'],
		];
		$this->assertEquals($expected, $result);
	}

	public static function formatProvider(): array
	{
		return [
			'date' => ['2024-10-29 07:32', 'date', '2024-10-29'],
			'datetime' => ['2024-10-29 07:32:56', 'datetime', '2024-10-29 07:32:56'],
			'datetime no seconds' => ['2024-10-29 07:32', 'datetime', '2024-10-29 07:32:00'],
			'int string to int' => ['123', 'int', 123],
			'int to int' => [123, 'int', 123],
			'float string to float' => ['123.456', 'float', 123.456],
			'float to float' => [123.456, 'float', 123.456],
			'neg int to bool' => [-1, 'bool', true],
			'zero int to bool' => [0, 'bool', false],
			'int string to bool' => ['1', 'bool', true],
			'bool to bool' => [true, 'bool', true],
			'empty string' => ['', 'string', ''],
			'string' => ['A text of some sort', 'string', 'A text of some sort'],
			'null value on string type' => [null, 'string', ''],
			'null value on empty type' => [null, '', null],
			'string value on number type' => ['value', 'number', 'value'],
		];
	}

	#[Test]
	#[DataProvider('formatProvider')]
	public function format(mixed $value, string $type, mixed $expected): void
	{
		$actual = Util::format($value, $type);
		$this->assertEquals($expected, $actual);
	}
}
