<?php

declare(strict_types=1);

namespace Projom\Storage\Facade\MySQL;

use Exception;

use Projom\Storage\Facade\MySQL\Query;
use Projom\Storage\SQL\Util\Aggregate;
use Projom\Storage\SQL\Util\Operator;
use Projom\Storage\MySQL\Util;

/**
 * Static repository - a trait that provides a set of methods to interact with a database table.
 * 
 * How to use:
 * * Use this trait to create a query-able "repository" of the class using the trait.
 * * The name of the class using the trait should be the same as the database table name.
 *
 * Optional methods to implement for additional processing:
 * * formatFields(): array [ 'Field' => 'string', 'AnotherField' => 'int', ... ]
 * * redactFields(): array [ 'Field', 'AnotherField' ]
 * 
 * The value of all redacted fields will be replaced with the string "__REDACTED__".
 */
trait Repository
{
	private const REDACTED = '__REDACTED__';

	/**
	 * Invoke / construct the repository.
	 */
	private static function invoke(): string
	{
		$table = static::table();
		if (! $table)
			throw new Exception('Table not set', 400);

		if (! static::primaryField())
			throw new Exception('Primary field not set', 400);

		return $table;
	}

	/**
	 * Returns the primary field.
	 * 
	 * Override this method to set a custom primary field.
	 * 
	 * Default: Primary field will be derived from the class name or namespace.
	 */
	public static function primaryField(): string
	{
		return Util::dynamicPrimaryField(static::class, static::useNamespaceAsTableName());
	}

	/**
	 * Returns the table name.
	 * 
	 * Override this method to set a custom table name.
	 * 
	 * Default: The table name will be derived from the class name or namespace.
	 */
	public static function table(): string
	{
		return Util::dynamicTableName(static::class, static::useNamespaceAsTableName());
	}

	/**
	 * Returns whether to use the namespace as the table name.
	 * 
	 * If true, the table name will be derived from the namespace of the class.
	 * Example: `App\Recipe\Ingredient\Repository` will become `RecipeIngredient`.
	 *
	 * If false, the table name will be derived from the class name.
	 * Example: `App\Recipe\IngredientRepository` will become `Ingredient`.
	 *  
	 * Default is false.
	 */
	public static function useNamespaceAsTableName(): bool
	{
		return false;
	}

	/**
	 * Returns which fields to format.
	 * 
	 * * Example: ['Name' => 'string', 'Price' => 'int']
	 */
	public static function formatFields(): array
	{
		return [];
	}

	/**
	 * Returns which fields to redact.
	 * 
	 * * Example: ['Email', 'Password']
	 */
	public static function redactFields(): array
	{
		return [];
	}

	/**
	 * Returns which fields to select.
	 * 
	 * * Example: ['Name', 'Email']
	 */
	public static function selectFields(): array
	{
		return [];
	}

	private static function processRecords(array $records): array
	{
		$primaryField = static::primaryField();
		$records = Util::rekey($records, $primaryField);

		$processedRecords = [];
		foreach ($records as $key => $record) {
			$record = static::selectRecordFields($record);
			$record = static::formatRecord($record);
			$record = static::redactRecord($record);
			$processedRecords[$key] = $record;
		}

		return $processedRecords;
	}

	private static function formatRecord(array $record): array
	{
		if (!$formatFields = static::formatFields())
			return $record;

		foreach ($formatFields as $field => $type) {
			if (!array_key_exists($field, $record))
				continue;
			$value = $record[$field];
			$record[$field] = Util::format($value, $type);
		}

		return $record;
	}

	private static function redactRecord(array $record): array
	{
		if (!$redactedFields = static::redactFields())
			return $record;

		foreach ($redactedFields as $field)
			if (array_key_exists($field, $record))
				$record[$field] = static::REDACTED;

		return $record;
	}

	private static function selectRecordFields(array $record): array
	{
		if (!$selectFields = static::selectFields())
			return $record;

		$modifiedRecord = [];
		foreach ($selectFields as $field)
			if (array_key_exists($field, $record))
				$modifiedRecord[$field] = $record[$field];

		return $modifiedRecord;
	}

	/**
	 * Create a record.
	 *
	 * * Example use: User::create(['Name' => 'John'])
	 */
	public static function create(array $record): int|string
	{
		$table = static::invoke();
		$primaryID = Query::build($table)->insert($record);
		return $primaryID;
	}

	/**
	 * Find a record by its primary id.
	 * 
	 * * Example use: User::find($userID = 3)
	 */
	public static function find(string|int $primaryID): null|array|object
	{
		$table = static::invoke();
		$primaryField = static::primaryField();
		$records = Query::build($table)->fetch($primaryField, $primaryID);
		if (!$records)
			return null;

		$records = static::processRecords($records);

		return array_pop($records);
	}

	/**
	 * Update a record by its primary id.
	 * 
	 * * Example use: User::update($userID = 3, ['Name' => 'A new name'])
	 */
	public static function update(string|int $primaryID, array $data): void
	{
		$table = static::invoke();
		$primaryField = static::primaryField();
		Query::build($table)->filterOn($primaryField, $primaryID)->update($data);
	}

	/**
	 * Delete a record by its primary id.
	 * 
	 * * Example use: User::delete($userID = 3)
	 */
	public static function delete(string|int $primaryID): void
	{
		$table = static::invoke();
		$primaryField = static::primaryField();
		Query::build($table)
			->filterOn($primaryField, $primaryID)
			->delete();
	}

	/**
	 * Delete records filtered on the given field and value.
	 * 
	 * * Example use: User::deleteWith('Email', 'john-doe@example.com')
	 */
	public static function deleteWith(string $field, mixed $value): void
	{
		$table = static::invoke();
		Query::build($table)
			->filterOn($field, $value)
			->delete();
	}

	/**
	 * Delete all records.
	 * 
	 * if no filters are provided, all records will be deleted.
	 * 
	 * * Example use: Log::deleteAll(filters: ['Type' => 'error'])
	 */
	public static function deleteAll(array $filters = []): void
	{
		$table = static::invoke();
		$query = Query::build($table);

		if ($filters)
			$query->filterOnFields($filters);

		$query->delete();
	}

	/**
	 * Clone a record.
	 * 
	 * @param array $newRecord used to write new values to fields from the cloned record.
	 * 
	 * * Example use: User::clone($userID = 3)
	 * * Example use: User::clone($userID = 3, ['Name' => 'New Name'])
	 */
	public static function clone(string|int $primaryID, array $newRecord = []): array|object
	{
		$table = static::invoke();
		$primaryField = static::primaryField();
		$records = Query::build($table)->fetch($primaryField, $primaryID);
		if (!$records)
			return throw new Exception('Record to clone not found', 400);

		$record = array_pop($records);
		unset($record[$primaryField]);

		// Merge new record with existing record. 
		$record = $newRecord + $record;
		$clonePrimaryID = Query::build($table)->insert($record);

		$clonedRecords = Query::build($table)->fetch($primaryField, $clonePrimaryID);
		$clonedRecords = static::processRecords($clonedRecords);
		return array_pop($clonedRecords);
	}

	/**
	 * Get all records.
	 * 
	 * * Example use: User::all()
	 * * Example use: User::all(filters: ['Active' => 0], sortOn: ['Name' => Sort::ASC])
	 */
	public static function all(array $filters = [], array $sortOn = []): null|array
	{
		$table = static::invoke();
		$query = Query::build($table);
		if ($filters)
			$query->filterOnFields($filters);

		if ($sortOn)
			$query->sortOn($sortOn);

		$records = $query->select();
		if (!$records)
			return null;

		$records = static::processRecords($records);

		return $records;
	}

	/**
	 * Search for records filtering on field like %value%.
	 * 
	 * * Example use: User::search('Name', 'John', ['Name' => Sort::ASC])
	 */
	public static function search(string $field, string $value, array $sortOn = []): null|array
	{
		$table = static::invoke();
		$query = Query::build($table)
			->filterOn($field, "%$value%", Operator::LIKE);

		if ($sortOn)
			$query->sortOn($sortOn);

		$records = $query->select();
		if (!$records)
			return null;

		$records = static::processRecords($records);

		return $records;
	}

	/**
	 * Get a record by filtering on field with value.
	 * 
	 * * Example use: User::get('Email', 'John.doe@example.com', ['Email' => Sort::ASC])
	 */
	public static function get(string $field, mixed $value, array $sortOn = []): null|array|object
	{
		$table = static::invoke();
		$query = Query::build($table);

		if ($sortOn)
			$query->sortOn($sortOn);

		$records = $query->fetch($field, $value);
		if (!$records)
			return null;

		$records = static::processRecords($records);

		if (count($records) === 1)
			return array_pop($records);

		return $records;
	}

	/**
	 * Count records.
	 * 
	 * * Example use: User::count()
	 * * Example use: User::count(filters: ['Active' => 0])
	 * * Example use: User::count('UserID', groupFields: ['Active'])
	 */
	public static function count(string $countField = '*',  array $filters = [], array $groupByFields = []): null|array
	{
		$table = static::invoke();
		$query = Query::build($table);

		if ($filters)
			$query->filterOnFields($filters);

		$aggregate = Aggregate::COUNT->buildSQL($countField, 'count');
		$fields = [$aggregate];

		if ($groupByFields) {
			$query->groupOn(...$groupByFields);
			$fields = Util::merge($fields, $groupByFields);
		}

		$records = $query->select(...$fields);
		if (!$records)
			return null;

		return $records;
	}

	/**
	 * Summarize records.
	 * 
	 * * Example use: Invoice::sum('Amount')
	 * * Example use: Invoice::sum('Amount', ['Paid' => 0, 'Due' => '2024-07-25'])
	 * * Example use: Invoice::sum('Amount', groupFields: ['Paid'])
	 */
	public static function sum(string $sumField, array $filters = [], array $groupByFields = []): null|array
	{
		$table = static::invoke();
		$query = Query::build($table);

		if ($filters)
			$query->filterOnFields($filters);

		$aggregate = Aggregate::SUM->buildSQL($sumField, 'sum');
		$fields = [$aggregate];

		if ($groupByFields) {
			$query->groupOn(...$groupByFields);
			$fields = Util::merge($fields, $groupByFields);
		}

		$records = $query->select(...$fields);
		if (!$records)
			return null;

		return $records;
	}

	/**
	 * Average records.
	 * 
	 * * Example use: Invoice::avg('Amount')
	 * * Example use: Invoice::avg('Amount', ['Paid' => 0, 'Due' => '2024-07-25'])
	 * * Example use: Invoice::avg('Amount', groupFields: ['Paid'])
	 */
	public static function avg(string $averageField, array $filters = [], array $groupByFields = []): null|array
	{
		$table = static::invoke();
		$query = Query::build($table);

		if ($filters)
			$query->filterOnFields($filters);

		$aggregate = Aggregate::AVG->buildSQL($averageField, 'avg');
		$fields = [$aggregate];

		if ($groupByFields) {
			$query->groupOn(...$groupByFields);
			$fields = Util::merge($fields, $groupByFields);
		}

		$records = $query->select(...$fields);
		if (!$records)
			return null;

		return $records;
	}

	/**
	 * Minimum of records.
	 * 
	 * * Example use: Invoice::min('Amount')
	 * * Example use: Invoice::min('Amount', ['Paid' => 0, 'Due' => '2024-07-25'])
	 * * Example use: Invoice::min('Amount', groupFields: ['Paid'])
	 */
	public static function min(string $minField, array $filters = [], array $groupByFields = []): null|array
	{
		$table = static::invoke();
		$query = Query::build($table);

		if ($filters)
			$query->filterOnFields($filters);

		$aggregate = Aggregate::MIN->buildSQL($minField, 'min');
		$fields = [$aggregate];

		if ($groupByFields) {
			$query->groupOn(...$groupByFields);
			$fields = Util::merge($fields, $groupByFields);
		}

		$records = $query->select(...$fields);
		if (!$records)
			return null;

		return $records;
	}

	/**
	 * Maximum of records.
	 * 
	 * * Example use: Invoice::max('Amount')
	 * * Example use: Invoice::max('Amount', ['Paid' => 0, 'Due' => '2024-07-25'])
	 * * Example use: Invoice::max('Amount', groupFields: ['Paid'])
	 */
	public static function max(string $maxField, array $filters = [], array $groupByFields = []): null|array
	{
		$table = static::invoke();
		$query = Query::build($table);

		if ($filters)
			$query->filterOnFields($filters);

		$aggregate = Aggregate::MAX->buildSQL($maxField, 'max');
		$fields = [$aggregate];

		if ($groupByFields) {
			$query->groupOn(...$groupByFields);
			$fields = Util::merge($fields, $groupByFields);
		}

		$records = $query->select(...$fields);
		if (!$records)
			return null;

		return $records;
	}

	/**
	 * Paginate records.
	 * 
	 * * Example use: User::paginate(1, 10)
	 * * Example use: User::paginate(1, 10, ['Name' => 'John'], ['Name' => Sort::ASC])
	 */
	public static function paginate(
		int $page,
		int $pageSize,
		array $filters = [],
		array $sortOn = []
	): null|array {
		$table = static::invoke();
		$query = Query::build($table);

		if ($filters)
			$query->filterOnFields($filters);

		if ($sortOn)
			$query->sortOn($sortOn);

		$offset = ($page - 1) * $pageSize;
		$query->offset($offset)->limit($pageSize);

		$records = $query->select();
		if (!$records)
			return null;

		$records = static::processRecords($records);

		return $records;
	}
}
