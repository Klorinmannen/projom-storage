<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Driver\MySQL;

use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Driver\MySQL\Util;

class UtilTest extends TestCase
{
	public function test_quote(): void
	{
		$this->assertEquals('`User`', Util::quote('User'));
		$this->assertEquals('*', Util::quote('*'));
	}

	public function test_quote_and_join(): void
	{
		$this->assertEquals('`User`,`Role`', Util::quoteAndJoin(['User', 'Role']));
	}

	public function test_quote_list(): void
	{
		$this->assertEquals(['`User`', '`Role`'], Util::quoteList(['User', 'Role']));
	}
}