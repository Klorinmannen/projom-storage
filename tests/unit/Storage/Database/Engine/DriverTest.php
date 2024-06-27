<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Engine;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\Driver;

class DriverTest extends TestCase
{
	#[Test]
	public function mysql(): void
	{
		$this->assertEquals('mysql', Driver::MySQL->value);
	}
}