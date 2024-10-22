<?php

declare(strict_types=1);

namespace Projom\Storage\MySQL;

use Projom\Storage\MySQL;
use Projom\Storage\SQL\Util\Operator;
use Projom\Storage\Util;

/**
 * The Model class serves as a base class for interacting with a mysql database table.
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
	public static function find(string|int $primaryID): null|array
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
	public static function clone(string|int $primaryID, array $newRecord = []): array
	{
		static::invoke();

		$records = MySQL::query(static::$class)->fetch(static::$primaryField, $primaryID);
		if (!$records)
			return throw new \Exception('Record to clone not found', 400);

		$record = array_pop($records);
		unset($record[static::$primaryField]);

		// Merge new record with existing record. Left-hand array takes precedence. 
		$record = $newRecord + $record;

		$clonePrimaryID = MySQL::query(static::$class)->insert($record);
		$clonedRecords = MySQL::query(static::$class)->fetch(static::$primaryField, $clonePrimaryID);

		return array_pop($clonedRecords);
	}

	/**
	 * Get all records.
	 * 
	 * * Example use: User::all()
	 * * Example use: User::all($filter1, $filter2)
	 */
	public static function all(array ...$filters): null|array
	{
		static::invoke();

		$query = MySQL::query(static::$class);

		if ($filters)
			$query->filterOnGroup($filters);

		$records = $query->select();
		if (!$records)
			return null;

		$keydRecords = Util::rekey($records, static::$primaryField);

		return $keydRecords;
	}

	/**
	 * Search for records filtering on field like value.
	 * 
	 * * Example use: User::search('Name', '%John%')
	 */
	public static function search(string $field, mixed $value): null|array
	{
		static::invoke();

		$records = MySQL::query(static::$class)->filterOn($field, $value, Operator::LIKE)->select();
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
	public static function get(string $field, mixed $value): null|array
	{
		static::invoke();

		$records = MySQL::query(static::$class)->fetch($field, $value);
		if (!$records)
			return null;

		$keydRecords = Util::rekey($records, static::$primaryField);

		return $keydRecords;
	}

	/**
	 * Count the number of records.
	 * 
	 * * Example use: User::count()
	 * * Example use: User::count($filter)
	 */
	public static function count(array $filter): null|int
	{
		static::invoke();

		$query = MySQL::query(static::$class);

		if ($filter !== null)
			$query->filterOnGroup([$filter]);

		$records = $query->select('COUNT(*) as count');
		if (!$records)
			return null;

		$record = array_pop($records);
		return (int) $record['count'];
	}
}
