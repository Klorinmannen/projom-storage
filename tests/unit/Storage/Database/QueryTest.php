<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Projom\Storage\Database\Driver\MySQL;
use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\Filter;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Collection;
use Projom\Storage\Database\Query\Operators;
use Projom\Storage\Database\Source\PDOSource;

class QueryTest extends TestCase
{
	public function test(): void
	{
		// For now ..
		$this->assertEquals(1, 1);
	}
}
