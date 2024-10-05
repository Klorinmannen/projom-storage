<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\MySQL;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\MySQL\LogicalOperator;

class LogicalOperatorTest extends TestCase
{
	#[Test]
	public function cases(): void
	{
		$expected = [
			LogicalOperator::AND,
			LogicalOperator::OR
		];
		$actual = LogicalOperator::cases();
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function values(): void
	{
		$expected = ['AND', 'OR'];
		$actual = LogicalOperator::values();
		$this->assertEquals($expected, $actual);
	}
}
