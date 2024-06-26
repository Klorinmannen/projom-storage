<?php

declare(strict_types=1);

namespace Projom\tests\unit\Storage\Database\Engine\Source;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Database\Engine\Source\PDO;

class PDOTest extends TestCase
{
	public static function parseAttributesProvider(): array
	{
		return [
			[
				'attributes' => [
					'ATTR_DEFAULT_FETCH_MODE' => 'FETCH_ASSOC',
					'ATTR_ERRMODE' => 'ERRMODE_EXCEPTION'
				],
				'expected' => [
					\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
					\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
				]
			]
		];
	}

	#[Test]
	#[DataProvider('parseAttributesProvider')]
	public function parseAttributes(array $attributes, array $expected): void
	{
		$result = PDO::parseAttributes($attributes);
		$this->assertEquals($expected, $result);
	}

	#[Test]
	public function parseAttributes_exception_bad_attribute(): void
	{
		$this->expectException(\Error::class);
		$this->expectExceptionMessage('Undefined constant PDO::DEFAULT_FETCH_MODE');
		PDO::parseAttributes([ 'DEFAULT_FETCH_MODE' => 'FETCH_ASSOC']);
	}

	#[Test]
	public function parseAttributes_exception_bad_value(): void
	{
		$this->expectException(\Error::class);
		$this->expectExceptionMessage('Undefined constant PDO::FETCH_DEFAULT_ASSOC');
		PDO::parseAttributes([ 'ATTR_DEFAULT_FETCH_MODE' => 'FETCH_DEFAULT_ASSOC']);
	}

	#[Test]
	public function default_pdo_attributes(): void
	{
		$defaultAttributes = [
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
		];
		
		$this->assertEquals(PDO::DEFAULT_PDO_ATTRIBUTES, $defaultAttributes);
	}
}