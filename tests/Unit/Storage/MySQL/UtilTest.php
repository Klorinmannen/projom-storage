<?php

declare(strict_types=1);

namespace JRF\Tests\Unit\Storage\MySQL;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use JRF\Storage\Internal\Database\Util;

class UtilTest extends TestCase
{
	public static function dynamicPrimaryFieldProvider(): array
	{
		return [
			'correct use namespace name' => [true, 'App\Recipe\Ingredient\Repository', 'RecipeIngredientID'],
			'malformed use namespace name' => [true, 'App\Recipe\IngredientRepository', 'RecipeID'],
			'correct class name 1' => [false, 'App\Recipe\IngredientRepository', 'IngredientID'],
			'correct class name 2' => [false, 'App\Recipe\Ingredient', 'IngredientID'],
			'malformed class name' => [false, 'App\Recipe\Ingredient\Repository', 'ID'],
		];
	}

	#[Test]
	#[DataProvider('dynamicPrimaryFieldProvider')]
	public function dynamicPrimaryField(bool $useNamespaceAsTableName, string $calledClass, string $expected): void
	{
		$result = Util::dynamicPrimaryField($calledClass, $useNamespaceAsTableName);
		$this->assertEquals($expected, $result);
	}

	public static function dynamicTableNameWithNamespaceProvider(): array
	{
		return [
			'correct use namespace name' => [true, 'App\Recipe\Ingredient\Repository', 'RecipeIngredient'],
			'malformed use namespace name' => [true, 'App\Recipe\IngredientRepository', 'Recipe'],
			'correct class name 1' => [false, 'App\Recipe\IngredientRepository', 'Ingredient'],
			'correct class name 2' => [false, 'App\Recipe\Ingredient', 'Ingredient'],
			'malformed class name' => [false, 'App\Recipe\Ingredient\Repository', ''],
		];
	}

	#[Test]
	#[DataProvider('dynamicTableNameWithNamespaceProvider')]
	public function dynamicTableNameWithNamespace(bool $useNamespaceAsTableName, string $calledClass, string $expected): void
	{
		$table = Util::dynamicTableName($calledClass, $useNamespaceAsTableName);
		$this->assertEquals($expected, $table);
	}

	#[Test]
	public function tableFromCalledClass(): void
	{
		$calledClass = 'App\Recipe\IngredientRepository';
		$class = Util::tableFromCalledClass($calledClass);
		$this->assertEquals('Ingredient', $class);
	}

	#[Test]
	public function tableFromNamespace(): void
	{
		$calledClass = 'App\Recipe\Ingredient\Repository';
		$table = Util::tableFromNamespace($calledClass);
		$this->assertEquals('RecipeIngredient', $table);
	}

	#[Test]
	public function replace(): void
	{
		$string = 'UserRepository';
		$expected = 'User';
		$result = Util::replace($string, ['Repository']);
		$this->assertEquals($expected, $result);
	}

		public static function formatProvider(): array
	{
		return [
			'date' => ['2024-10-29 07:32', 'date', '2024-10-29'],
			'datetime' => ['2024-10-29 07:32:56', 'datetime', '2024-10-29 07:32:56'],
			'datetime no seconds' => ['2024-10-29 07:32', 'datetime', '2024-10-29 07:32:00'],
			'int string to int' => ['123', 'int', 123],
			'int to int' => [123, 'int', 123],
			'float string to float' => ['123.456', 'float', 123.456],
			'float to float' => [123.456, 'float', 123.456],
			'neg int to bool' => [-1, 'bool', true],
			'zero int to bool' => [0, 'bool', false],
			'int string to bool' => ['1', 'bool', true],
			'bool to bool' => [true, 'bool', true],
			'empty string' => ['', 'string', ''],
			'string' => ['A text of some sort', 'string', 'A text of some sort'],
			'null value on string type' => [null, 'string', ''],
			'null value on empty type' => [null, '', null],
			'string value on number type' => ['value', 'number', 'value'],
		];
	}

	#[Test]
	#[DataProvider('formatProvider')]
	public function format(mixed $value, string $type, mixed $expected): void
	{
		$actual = Util::format($value, $type);
		$this->assertEquals($expected, $actual);
	}
}
