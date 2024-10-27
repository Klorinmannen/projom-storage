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
	private static $class = null;
	private static $primaryField = null;

	private static function invoke()
	{
		if (static::$class !== null)
			return;

		static::$class = basename(get_called_class());

		if (!defined(static::$class . '::PRIMARY_FIELD'))
			throw new \Exception('PRIMARY_FIELD constant not defined', 400);

		static::$primaryField = static::$class::PRIMARY_FIELD;
	}

	/**
	 * Create a record.
	 * 
	 * * Example use: User::create(['Name' => 'John'])
	 */
	public static function create(array $record): int|string
	{
		static::invoke();
		$primaryID = MySQL::query(static::$class)->insert($record);
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

		$records = MySQL::query(static::$class)->fetch(static::$primaryField, $primaryID);
		if (!$records)
			return null;

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
		MySQL::query(static::$class)->filterOn(static::$primaryField, $primaryID)->update($data);
	}

	/**
	 * Delete a record filtering on primary id.
	 * 
	 * * Example use: User::delete($userID = 3)
	 */
	public static function delete(string|int $primaryID): void
	{
		static::invoke();
		MySQL::query(static::$class)->filterOn(static::$primaryField, $primaryID)->delete();
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

		$records = MySQL::query(static::$class)->fetch(static::$primaryField, $primaryID);
		if (!$records)
			return throw new \Exception('Record to clone not found', 400);

		$record = array_pop($records);
		unset($record[static::$primaryField]);

		// Merge new record with existing record. 
		$record = $newRecord + $record;

		$clonePrimaryID = MySQL::query(static::$class)->insert($record);
		$clonedRecords = MySQL::query(static::$class)->fetch(static::$primaryField, $clonePrimaryID);

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

		$query = MySQL::query(static::$class);
		if ($filters)
			$query->filterOnFields($filters);

		$records = $query->select();
		if (!$records)
			return null;

		$keydRecords = Util::rekey($records, static::$primaryField);

		return $keydRecords;
	}

	/**
	 * Search for records filtering on field like %value%.
	 * 
	 * * Example use: User::search('Name', 'John')
	 */
	public static function search(string $field, string $value): null|array
	{
		static::invoke();

		$records = MySQL::query(static::$class)->filterOn($field, "%$value%", Operator::LIKE)->select();
		if (!$records)
			return null;

		$keydRecords = Util::rekey($records, static::$primaryField);

		return $keydRecords;
	}

	/**
	 * Find a record by filtering on field with value.
	 * 
	 * * Example use: User::get('Email', 'John.doe@example.com')
	 */
	public static function get(string $field, mixed $value): null|array|object
	{
		static::invoke();

		$records = MySQL::query(static::$class)->fetch($field, $value);
		if (!$records)
			return null;

		if (count($records) === 1)
			return array_pop($records);

		$keydRecords = Util::rekey($records, static::$primaryField);

		return $keydRecords;
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

		$query = MySQL::query(static::$class);

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

		$query = MySQL::query(static::$class);

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

		$query = MySQL::query(static::$class);

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

		$query = MySQL::query(static::$class);

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

		$query = MySQL::query(static::$class);

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

		$query = MySQL::query(static::$class);

		if ($filters)
			$query->filterOnFields($filters);

		$offset = ($page - 1) * $pageSize;
		$query->offset($offset)->limit($pageSize);

		$records = $query->select();
		if (!$records)
			return null;

		$keydRecords = Util::rekey($records, static::$primaryField);

		return $keydRecords;
	}
}
