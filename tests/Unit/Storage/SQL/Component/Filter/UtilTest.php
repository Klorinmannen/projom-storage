<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\SQL\Component\Filter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\SQL\Component\Filter\Util;

class UtilTest extends TestCase
{
	public static function parameterNameProvider(): array
	{
		return [
			[
				[],
				1,
				'filter__1'
			],
			[
				['UserID'],
				1,
				'filter_userid_1'
			],
			[
				['UserID', 'Name'],
				2,
				'filter_userid_name_2',
			],
			[
				['User.UserID', 'User.Name'],
				3,
				'filter_user_userid_user_name_3'
			]
		];
	}

	#[Test]
	#[DataProvider('parameterNameProvider')]
	public function parameterName(array $columns, int $id, string $expected): void
	{
		$actual = Util::parameterName($columns, $id);
		$this->assertEquals($expected, $actual);
	}

	public static function addParenthesesProvider(): array
	{
		return [
			[
				[],
				'expected' => []
			],
			[
				['UserID'],
				'expected' => ['UserID']
			],
			[
				['UserID', 'Name'],
				'expected' => ['(', 'UserID', 'Name', ')']
			],
			[
				['UserID', 'Name', 'Email'],
				'expected' => ['(', 'UserID', 'Name', 'Email', ')']
			]
		];
	}

	#[Test]
	#[DataProvider('addParenthesesProvider')]
	public function addParentheses(array $filter, array $expected): void
	{
		$actual = Util::addParentheses($filter);
		$this->assertEquals($expected, $actual);
	}
}
