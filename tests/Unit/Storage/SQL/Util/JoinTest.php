<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL\Util;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\SQL\Util\Join;

class JoinTest extends TestCase
{
	#[Test]
	public function cases(): void
	{
		$expected = [
			Join::INNER,
			Join::LEFT,
			Join::RIGHT,
			Join::FULL,
			Join::CROSS,
			Join::STRAIGHT,
			Join::OUTER,
			Join::NATURAL
		];
		$actual = Join::cases();
		$this->assertEquals($expected, $actual);
	}

	#[Test]
	public function values(): void
	{
		$expected = ['INNER JOIN', 'LEFT JOIN', 'RIGHT JOIN', 'FULL JOIN', 'CROSS JOIN', 'STRAIGHT JOIN', 'OUTER JOIN', 'NATURAL JOIN'];
		$actual = Join::values();
		$this->assertEquals($expected, $actual);
	}
}