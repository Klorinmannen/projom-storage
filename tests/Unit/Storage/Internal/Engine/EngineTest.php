<?php

declare(strict_types=1);

namespace JRF\Tests\Unit\Storage\Internal\Engine;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use JRF\Storage\Internal\Engine\EngineType;

class EngineTest extends TestCase
{
	#[Test]
	public function mysql(): void
	{
		$this->assertEquals('mysql', EngineType::MySQL->value);
	}
}