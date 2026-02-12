<?php

declare(strict_types=1);

namespace JRF\Storage\Database;

use Exception;

use JRF\Storage\Database\Query;
use JRF\Storage\Database\Util\Aggregate;
use JRF\Storage\Database\Util\Operator;
use JRF\Storage\Internal\Database\Util;
use JRF\Storage\Internal\Database\SQL\Statement\Builder;

/**
 * Static repository - a trait that provides a set of methods to interact with a database table.
 */
trait Repository
{
	/**
	 * Invoke the repository.
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

	/**
	 * Returns which fields to translate.
	 * 
	 * * Example: ['Name' => 'translated_name', 'Description' => 'translated_description']
	 */
	public static function translateFields(): array
	{
		return [];
	}

	/**
	 * Returns whether to rekey the records with the primary field.
	 * 
	 * Default is false.
	 */
	public static function rekeyWithPrimaryField(): bool
	{
		return false;
	}

	private static function processRecords(array $records): array
	{
		$options = static::processOptions();
		$processedRecords = Util::processRecords($records, $options);
		return $processedRecords;
	}

	private static function processOptions(): array
	{
		$primaryField = static::rekeyWithPrimaryField() ? static::primaryField() : '';
		return [
			'select_fields' => static::selectFields(),
			'format_fields' => static::formatFields(),
			'redact_fields' => static::redactFields(),
			'translate_fields' => static::translateFields(),
			'rekey_with_primary_field' => $primaryField
		];
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
	 * @param array $newRecord used to write new values to the new, cloned, record.
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
		return static::aggregate($countField, 'COUNT', $filters, $groupByFields);
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
		return static::aggregate($sumField, 'SUM', $filters, $groupByFields);
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
		return static::aggregate($averageField, 'AVG', $filters, $groupByFields);
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
		return static::aggregate($minField, 'MIN', $filters, $groupByFields);
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
		return static::aggregate($maxField, 'MAX', $filters, $groupByFields);
	}

	private static function aggregate(
		string $field,
		string $aggregate,
		array $filters = [],
		array $groupByFields = []
	): null|array {

		$table = static::invoke();
		$query = Query::build($table);

		if ($filters)
			$query->filterOnFields($filters);

		$aggregate = Aggregate::from(strtoupper($aggregate))->buildSQL($field, strtolower($aggregate));
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
	 * * Example use: User::paginate(1, 10, ['Name' => Sort::ASC])
	 * * Example use: User::paginate(1, 10, ['Name' => Sort::ASC], ['Name' => 'John'])
	 */
	public static function paginate(
		int $page,
		int $pageSize,
		array $sortOn,
		array $filters = [],
	): null|array {

		$table = static::invoke();
		$query = Query::build($table);

		$query->sortOn($sortOn);

		if ($filters)
			$query->filterOnFields($filters);

		$offset = ($page - 1) * $pageSize;
		$query->offset($offset)->limit($pageSize);

		$records = $query->select();
		if (!$records)
			return null;

		$records = static::processRecords($records);

		return $records;
	}

	/**
	 * Build a query with the repository.
	 * 
	 * * Example use: User::query()->filterOn('Name', 'John')->select();
	 */
	public static function query(): Builder
	{
		$table = static::invoke();
		return Query::build($table);
	}
}
