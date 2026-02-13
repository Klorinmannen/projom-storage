<?php

declare(strict_types=1);

namespace JRF\Tests\Unit\Storage\Internal;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use JRF\Storage\Internal\Util;

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

	public static function stringToArrayProvider(): array
	{
		return [
			['string', ['string']],
			[['array'], ['array']],
		];
	}

	#[Test]
	#[DataProvider('stringToArrayProvider')]
	public function stringToArray(string|array $subject, array $expected): void
	{
		$actual = Util::stringToArray($subject);
		$this->assertEquals($expected, $actual);
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
}
