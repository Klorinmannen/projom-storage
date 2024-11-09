<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Query\Util;

class UtilTest extends TestCase
{
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
}