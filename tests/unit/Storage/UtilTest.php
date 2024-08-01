<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Util;

class UtilTest extends TestCase
{
	public static function stringToListProvider(): array
	{
		return [
			['123', ',', ['123']],
			['123, 456', ',',['123', '456']],
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
}