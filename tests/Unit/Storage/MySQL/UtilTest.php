<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\MySQL;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\MySQL\Util;

class UtilTest extends TestCase
{
	#[Test]
	public function classFromCalledClass(): void
	{
		$calledClass = 'Projom\Storage\MySQL\Util';
		$class = Util::classFromCalledClass($calledClass);
		$this->assertEquals('Util', $class);
	}

	#[Test]
	public function replace(): void
	{
		$string = 'UserRepository';
		$expected = 'User';
		$result = Util::replace($string, ['Repository']);
		$this->assertEquals($expected, $result);
	}
}
