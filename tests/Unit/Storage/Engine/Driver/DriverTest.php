<?php

declare(strict_types=1);

namespace JRF\Tests\Unit\Storage\Engine\Driver;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use JRF\Storage\Internal\Engine\Driver\Driver;

class DriverTest extends TestCase
{
	#[Test]
	public function mysql(): void
	{
		$this->assertEquals('mysql', Driver::MySQL->value);
	}
}