<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Language\SQL;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Language\SQL\Util;

class UtilTest extends TestCase
{
	#[Test]
	public function quoteList(): void
	{
		$result = Util::quoteList(['User', 'Role']);
		$expected = ['`User`', '`Role`'];
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function quote(): void
	{
		$result = Util::quote('User');
		$expected = '`User`';
		$this->assertEquals($expected, $result);
		
		$result = Util::quote('*');
		$expected = '*';
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function quoteAndJoin(): void
	{
		$result = Util::quoteAndJoin(['User', 'Role']);
		$expected = '`User`,`Role`';
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function splitThenQuoteAndJoin(): void
	{
		$result = Util::splitThenQuoteAndJoin(' User.RoleID ', '.');
		$expected = '`User`.`RoleID`';
		$this->assertEquals($expected, $result);
	}
}