<?php

declare(strict_types=1);

namespace Projom\Storage\Model;

use Projom\Storage\Query\MySQLQuery;
use Projom\Storage\SQL\Util\Aggregate;
use Projom\Storage\SQL\Util\Operator;
use Projom\Storage\Util;

/**
 * This class provides a set of methods to interact with a database table.
 * Extend this class to create a query-able "model" for that table.
 * The extended class name should be the same as the table name.
 *
 * Required constants: 
 * * PRIMARY_FIELD = 'FieldID'
 * 
 * Additional processing can be done on fields by defining the following optional constants:
 * * FORMAT_FIELDS = [ 'Field' => 'Type' ]
 * * REDACTED_FIELDS = [ 'Field', 'AnotherField' ]
 */
class MySQLModel
{
	private static $table = null;
	private static $primaryField = null;
	private static $formatFields = [];
	private static $redactedFields = [];

	private static function invoke()
	{
		if (static::$table !== null)
			return;

		$calledClass = get_called_class();
		$class = str_replace('\\', '/', $calledClass);
		$class = basename($class);
		static::$table = $class;

		if (!defined("{$calledClass}::PRIMARY_FIELD"))
			throw new \Exception('PRIMARY_FIELD constant not defined', 400);
		static::$primaryField = $calledClass::PRIMARY_FIELD;

		if (defined("{$calledClass}::FORMAT_FIELDS"))
			static::$formatFields = $calledClass::FORMAT_FIELDS;

		if (defined("{$calledClass}::REDACTED_FIELDS"))
			static::$redactedFields = $calledClass::REDACTED_FIELDS;
	}

	private static function processRecords(array $records): array
	{
		$records = Util::rekey($records, static::$primaryField);

		$processedRecords = [];
		foreach ($records as $key => $record) {
			$record = static::formatRecord($record);
			$record = static::redactRecord($record);
			$processedRecords[$key] = $record;
		}

		return $processedRecords;
	}

	private static function formatRecord(array $record): array
	{
		if (!static::$formatFields)
			return $record;

		foreach (static::$formatFields as $field => $type) {
			if (!array_key_exists($field, $record))
				continue;
			$value = $record[$field];
			$record[$field] = Util::format($value, $type);
		}

		return $record;
	}

	private static function redactRecord(array $record): array
	{
		if (!static::$redactedFields)
			return $record;

		foreach (static::$redactedFields as $field) {
			if (!array_key_exists($field, $record))
				throw new \Exception("Field: {$field}, could not be redacted. Not found in record", 400);
			$record[$field] = '__REDACTED__';
		}

		return $record;
	}

	/**
	 * Create a record.
	 * 
	 * * Example use: User::create(['Name' => 'John'])
	 */
	public static function create(array $record): int|string
	{
		static::invoke();
		$primaryID = MySQLQuery::query(static::$table)->insert($record);
		return $primaryID;
	}

	/**
	 * Find a record by its primary id.
	 * 
	 * * Example use: User::find($userID = 3)
	 */
	public static function find(string|int $primaryID): null|array|object
	{
		static::invoke();

		$records = MySQLQuery::query(static::$table)->fetch(static::$primaryField, $primaryID);
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
		static::invoke();
		MySQLQuery::query(static::$table)->filterOn(static::$primaryField, $primaryID)->update($data);
	}

	/**
	 * Delete a record by its primary id.
	 * 
	 * * Example use: User::delete($userID = 3)
	 */
	public static function delete(string|int $primaryID): void
	{
		static::invoke();
		MySQLQuery::query(static::$table)->filterOn(static::$primaryField, $primaryID)->delete();
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
		static::invoke();

		$records = MySQLQuery::query(static::$table)->fetch(static::$primaryField, $primaryID);
		if (!$records)
			return throw new \Exception('Record to clone not found', 400);

		$record = array_pop($records);
		unset($record[static::$primaryField]);

		// Merge new record with existing record. 
		$record = $newRecord + $record;
		$clonePrimaryID = MySQLQuery::query(static::$table)->insert($record);
		
		$clonedRecords = MySQLQuery::query(static::$table)->fetch(static::$primaryField, $clonePrimaryID);
		$clonedRecords = static::processRecords($clonedRecords);
		return array_pop($clonedRecords);
	}

	/**
	 * Get all records.
	 * 
	 * * Example use: User::all()
	 * * Example use: User::all($filters = ['Active' => 0])
	 */
	public static function all(array $filters = []): null|array
	{
		static::invoke();

		$query = MySQLQuery::query(static::$table);
		if ($filters)
			$query->filterOnFields($filters);

		$records = $query->select();
		if (!$records)
			return null;

		$records = static::processRecords($records);

		return $records;
	}

	/**
	 * Search for records filtering on field like %value%.
	 * 
	 * * Example use: User::search('Name', 'John')
	 */
	public static function search(string $field, string $value): null|array
	{
		static::invoke();

		$records = MySQLQuery::query(static::$table)->filterOn($field, "%$value%", Operator::LIKE)->select();
		if (!$records)
			return null;

		$records = static::processRecords($records);

		return $records;
	}

	/**
	 * Get a record by filtering on field with value.
	 * 
	 * * Example use: User::get('Email', 'John.doe@example.com')
	 */
	public static function get(string $field, mixed $value): null|array|object
	{
		static::invoke();

		$records = MySQLQuery::query(static::$table)->fetch($field, $value);
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
		static::invoke();

		$query = MySQLQuery::query(static::$table);

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
		static::invoke();

		$query = MySQLQuery::query(static::$table);

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
		static::invoke();

		$query = MySQLQuery::query(static::$table);

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
		static::invoke();

		$query = MySQLQuery::query(static::$table);

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
		static::invoke();

		$query = MySQLQuery::query(static::$table);

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
	 * * Example use: User::paginate(1, 10, ['Name' => 'John'])
	 */
	public static function paginate(int $page, int $pageSize, array $filters = []): null|array
	{
		static::invoke();

		$query = MySQLQuery::query(static::$table);

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
}
