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

	public static function selectRecordFieldsProvider(): array
	{
		return [
			'empty select fields' => [
				['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
				[],
				['id' => 1, 'name' => 'John', 'email' => 'john@example.com']
			],
			'select single field' => [
				['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
				['name'],
				['name' => 'John']
			],
			'select multiple fields' => [
				['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
				['id', 'name'],
				['id' => 1, 'name' => 'John']
			],
			'select non-existent field' => [
				['id' => 1, 'name' => 'John'],
				['email'],
				[]
			],
			'select mix of existing and non-existent fields' => [
				['id' => 1, 'name' => 'John'],
				['id', 'email', 'name'],
				['id' => 1, 'name' => 'John']
			],
		];
	}

	#[Test]
	#[DataProvider('selectRecordFieldsProvider')]
	public function selectRecordFields(array $record, array $selectFields, array $expected): void
	{
		$result = Util::selectRecordFields($record, $selectFields);
		$this->assertEquals($expected, $result);
	}

	public static function formatRecordProvider(): array
	{
		return [
			'empty format fields' => [
				['id' => '1', 'name' => 'John'],
				[],
				['id' => '1', 'name' => 'John']
			],
			'format single field to int' => [
				['id' => '123', 'name' => 'John'],
				['id' => 'int'],
				['id' => 123, 'name' => 'John']
			],
			'format multiple fields' => [
				['id' => '123', 'price' => '99.99', 'active' => '1'],
				['id' => 'int', 'price' => 'float', 'active' => 'bool'],
				['id' => 123, 'price' => 99.99, 'active' => true]
			],
			'format non-existent field' => [
				['id' => 1, 'name' => 'John'],
				['email' => 'string'],
				['id' => 1, 'name' => 'John']
			],
			'format date field' => [
				['created_at' => '2024-10-29 07:32:56'],
				['created_at' => 'date'],
				['created_at' => '2024-10-29']
			],
		];
	}

	#[Test]
	#[DataProvider('formatRecordProvider')]
	public function formatRecord(array $record, array $formatFields, array $expected): void
	{
		$result = Util::formatRecord($record, $formatFields);
		$this->assertEquals($expected, $result);
	}

	public static function redactRecordProvider(): array
	{
		return [
			'empty redact fields' => [
				['id' => 1, 'password' => 'secret123'],
				[],
				'__REDACTED__',
				['id' => 1, 'password' => 'secret123']
			],
			'redact single field' => [
				['id' => 1, 'password' => 'secret123'],
				['password'],
				'__REDACTED__',
				['id' => 1, 'password' => '__REDACTED__']
			],
			'redact multiple fields' => [
				['id' => 1, 'password' => 'secret123', 'token' => 'abc123'],
				['password', 'token'],
				'__REDACTED__',
				['id' => 1, 'password' => '__REDACTED__', 'token' => '__REDACTED__']
			],
			'redact non-existent field' => [
				['id' => 1, 'name' => 'John'],
				['password'],
				'__REDACTED__',
				['id' => 1, 'name' => 'John']
			],
			'custom redact text' => [
				['id' => 1, 'password' => 'secret123'],
				['password'],
				'***HIDDEN***',
				['id' => 1, 'password' => '***HIDDEN***']
			],
		];
	}

	#[Test]
	#[DataProvider('redactRecordProvider')]
	public function redactRecord(array $record, array $redactFields, string $redactText, array $expected): void
	{
		$result = Util::redactRecord($record, $redactFields, $redactText);
		$this->assertEquals($expected, $result);
	}

	public static function translateRecordFieldsProvider(): array
	{
		return [
			'empty translate fields' => [
				['id' => 1, 'name' => 'John'],
				[],
				['id' => 1, 'name' => 'John']
			],
			'translate single field' => [
				['id' => 1, 'name' => 'John'],
				['id' => 'user_id'],
				['user_id' => 1]
			],
			'translate multiple fields' => [
				['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
				['id' => 'user_id', 'name' => 'full_name'],
				['user_id' => 1, 'full_name' => 'John']
			],
			'translate non-existent field' => [
				['id' => 1, 'name' => 'John'],
				['email' => 'user_email'],
				[]
			],
			'translate mix of existing and non-existent fields' => [
				['id' => 1, 'name' => 'John'],
				['id' => 'user_id', 'email' => 'user_email', 'name' => 'full_name'],
				['user_id' => 1, 'full_name' => 'John']
			],
		];
	}

	#[Test]
	#[DataProvider('translateRecordFieldsProvider')]
	public function translateRecordFields(array $record, array $translateFields, array $expected): void
	{
		$result = Util::translateRecordFields($record, $translateFields);
		$this->assertEquals($expected, $result);
	}

	public static function processRecordsProvider(): array
	{
		return [
			'empty records' => [
				[],
				[],
				[]
			],
			'no options' => [
				[['id' => 1, 'name' => 'John']],
				[],
				[['id' => 1, 'name' => 'John']]
			],
			'select fields only' => [
				[['id' => 1, 'name' => 'John', 'email' => 'john@example.com']],
				['select_fields' => ['id', 'name']],
				[['id' => 1, 'name' => 'John']]
			],
			'format fields only' => [
				[['id' => '123', 'price' => '99.99']],
				['format_fields' => ['id' => 'int', 'price' => 'float']],
				[['id' => 123, 'price' => 99.99]]
			],
			'redact fields only' => [
				[['id' => 1, 'password' => 'secret123']],
				['redact_fields' => ['password']],
				[['id' => 1, 'password' => '__REDACTED__']]
			],
			'translate fields only' => [
				[['id' => 1, 'name' => 'John']],
				['translate_fields' => ['id' => 'user_id', 'name' => 'full_name']],
				[['user_id' => 1, 'full_name' => 'John']]
			],
			'rekey with primary field' => [
				[['id' => 1, 'name' => 'John'], ['id' => 2, 'name' => 'Jane']],
				['rekey_with_primary_field' => 'id'],
				[1 => ['id' => 1, 'name' => 'John'], 2 => ['id' => 2, 'name' => 'Jane']]
			],
			'multiple operations combined' => [
				[['id' => '1', 'name' => 'John', 'email' => 'john@example.com', 'password' => 'secret']],
				[
					'select_fields' => ['id', 'name', 'password'],
					'format_fields' => ['id' => 'int'],
					'redact_fields' => ['password']
				],
				[['id' => 1, 'name' => 'John', 'password' => '__REDACTED__']]
			],
			'all operations combined' => [
				[
					['userID' => '1', 'username' => 'john', 'email' => 'john@example.com', 'password' => 'secret'],
					['userID' => '2', 'username' => 'jane', 'email' => 'jane@example.com', 'password' => 'pass123']
				],
				[
					'select_fields' => ['userID', 'username', 'password'],
					'format_fields' => ['userID' => 'int'],
					'redact_fields' => ['password'],
					'translate_fields' => ['userID' => 'id', 'username' => 'name'],
					'rekey_with_primary_field' => 'id'
				],
				[
					1 => ['id' => 1, 'name' => 'john'],
					2 => ['id' => 2, 'name' => 'jane']
				]
			],
		];
	}

	#[Test]
	#[DataProvider('processRecordsProvider')]
	public function processRecords(array $records, array $options, array $expected): void
	{
		$result = Util::processRecords($records, $options);
		$this->assertEquals($expected, $result);
	}
}
