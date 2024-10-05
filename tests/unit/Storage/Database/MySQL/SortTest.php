<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\MySQL;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\MySQL\Sort;

class SortTest extends TestCase
{
	#[Test]
	public function cases(): void
	{
		$expected = [
			Sort::ASC,
			Sort::DESC
		];
		$actual = Sort::cases();
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function values(): void
	{
		$expected = ['ASC', 'DESC'];
		$actual = Sort::values();
		$this->assertEquals($expected, $actual);
	}
}