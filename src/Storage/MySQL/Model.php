<?php

declare(strict_types=1);

namespace Projom\Storage\MySQL;

use Projom\Storage\MySQL;
use Projom\Storage\SQL\Util\Aggregate;
use Projom\Storage\SQL\Util\Operator;
use Projom\Storage\Util;

/**
 * The Model class serves as a base class for a class representing a database table.
 * 
 * Create a class named as the table in the database and extend this class.
 * Define the PRIMARY_FIELD constant as the name of the primary field from the table.
 */
class Model
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

		$formattedRecord = [];
		foreach ($record as $field => $value) {
			if (array_key_exists($field, static::$formatFields)) {
				$type = static::$formatFields[$field];
				$formattedRecord[$field] = Util::format($value, $type);
			} else {
				$formattedRecord[$field] = $value;
			}
		}

		var_dump($formattedRecord);

		return $formattedRecord;
	}

	private static function redactRecord(array $record): array
	{
		if (!static::$redactedFields)
			return $record;

		$redactedRecord = [];
		foreach ($record as $field => $value) {
			if (in_array($field, static::$redactedFields))
				$redactedRecord[$field] = '___REDACTED___';
			else
				$redactedRecord[$field] = $value;
		}

		return $redactedRecord;
	}

	/**
	 * Create a record.
	 * 
	 * * Example use: User::create(['Name' => 'John'])
	 */
	public static function create(array $record): int|string
	{
		static::invoke();
		$primaryID = MySQL::query(static::$table)->insert($record);
		return $primaryID;
	}

	/**
	 * Get a record filtering on primary id.
	 * 
	 * * Example use: User::find($userID = 3)
	 */
	public static function find(string|int $primaryID): null|array|object
	{
		static::invoke();

		$records = MySQL::query(static::$table)->fetch(static::$primaryField, $primaryID);
		if (!$records)
			return null;

		$records = static::processRecords($records);

		return array_pop($records);
	}

	/**
	 * Update a record filtering on primary id.
	 * 
	 * * Example use: User::update($userID = 3, ['Name' => 'A new name'])
	 */
	public static function update(string|int $primaryID, array $data): void
	{
		static::invoke();
		MySQL::query(static::$table)->filterOn(static::$primaryField, $primaryID)->update($data);
	}

	/**
	 * Delete a record filtering on primary id.
	 * 
	 * * Example use: User::delete($userID = 3)
	 */
	public static function delete(string|int $primaryID): void
	{
		static::invoke();
		MySQL::query(static::$table)->filterOn(static::$primaryField, $primaryID)->delete();
	}

	/**
	 * Clone a record.
	 * 
	 * * Example use: User::clone($userID = 3)
	 * * Example use: User::clone($userID = 3, ['Name' => 'New Name'])
	 */
	public static function clone(string|int $primaryID, array $newRecord = []): array|object
	{
		static::invoke();

		$records = MySQL::query(static::$table)->fetch(static::$primaryField, $primaryID);
		if (!$records)
			return throw new \Exception('Record to clone not found', 400);

		$record = array_pop($records);
		unset($record[static::$primaryField]);

		// Merge new record with existing record. 
		$record = $newRecord + $record;

		$clonePrimaryID = MySQL::query(static::$table)->insert($record);
		$clonedRecords = MySQL::query(static::$table)->fetch(static::$primaryField, $clonePrimaryID);

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

		$query = MySQL::query(static::$table);
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

		$records = MySQL::query(static::$table)->filterOn($field, "%$value%", Operator::LIKE)->select();
		if (!$records)
			return null;

		$records = static::processRecords($records);

		return $records;
	}

	/**
	 * Find a record by filtering on field with value.
	 * 
	 * * Example use: User::get('Email', 'John.doe@example.com')
	 */
	public static function get(string $field, mixed $value): null|array|object
	{
		static::invoke();

		$records = MySQL::query(static::$table)->fetch($field, $value);
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

		$query = MySQL::query(static::$table);

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
	 * Sum records.
	 * 
	 * * Example use: Invoice::sum('Amount')
	 * * Example use: Invoice::sum('Amount', ['Paid' => 0, 'Due' => '2024-07-25'])
	 * * Example use: Invoice::sum('Amount', groupFields: ['Paid'])
	 */
	public static function sum(string $sumField, array $filters = [], array $groupByFields = []): null|array
	{
		static::invoke();

		$query = MySQL::query(static::$table);

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

		$query = MySQL::query(static::$table);

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
	 * Min value of records.
	 * 
	 * * Example use: Invoice::min('Amount')
	 * * Example use: Invoice::min('Amount', ['Paid' => 0, 'Due' => '2024-07-25'])
	 * * Example use: Invoice::min('Amount', groupFields: ['Paid'])
	 */
	public static function min(string $minField, array $filters = [], array $groupByFields = []): null|array
	{
		static::invoke();

		$query = MySQL::query(static::$table);

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
	 * Max value of records.
	 * 
	 * * Example use: Invoice::max('Amount')
	 * * Example use: Invoice::max('Amount', ['Paid' => 0, 'Due' => '2024-07-25'])
	 * * Example use: Invoice::max('Amount', groupFields: ['Paid'])
	 */
	public static function max(string $maxField, array $filters = [], array $groupByFields = []): null|array
	{
		static::invoke();

		$query = MySQL::query(static::$table);

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

		$query = MySQL::query(static::$table);

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
