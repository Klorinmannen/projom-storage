<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\MySQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Projom\Storage\Database\MySQL\Operator;

class OperatorTest extends TestCase
{
	#[Test]
	public function cases(): void
	{
		$expected = [
			Operator::EQ,
			Operator::NE,
			Operator::GT,
			Operator::GTE,
			Operator::LT,
			Operator::LTE,
			Operator::LIKE,
			Operator::NOT_LIKE,
			Operator::IN,
			Operator::NOT_IN,
			Operator::IS_NULL,
			Operator::IS_NOT_NULL
		];
		$actual = Operator::cases();
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function values(): void
	{
		$expected = ['=', '<>', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'IS NULL', 'IS NOT NULL'];
		$actual = Operator::values();
		$this->assertEquals($expected, $actual);
	}
}
