<?php

declare(strict_types=1);

namespace Projom\Storage\MySQL;

use Projom\Storage\MySQL;
use Projom\Storage\SQL\Util\Filter;
use Projom\Storage\SQL\Util\Operator;
use Projom\Storage\Util;

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
	 * Find a record.
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
	 * Get all records.
	 * 
	 * * Example use: User::all()
	 */
	public static function all(): null|array
	{
		static::invoke();

		$records = MySQL::query(static::$class)->select();
		if (!$records)
			return null;

		$keydRecords = Util::rekey($records, static::$primaryField);

		return $keydRecords;
	}

	/**
	 * Get a record.
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
	 * Update a record.
	 * 
	 * * Example use: User::update($userID = 3, ['Name' => 'A new name'])
	 */
	public static function update(string|int $primaryID, array $data): int
	{
		static::invoke();

		$rowsAffected = MySQL::query(static::$class)->filterOn(static::$primaryField, $primaryID)->update($data);

		return $rowsAffected;
	}

	/**
	 * Delete a record.
	 * 
	 * * Example use: User::delete($userID = 3)
	 */
	public static function delete(string|int $primaryID): void
	{
		static::invoke();

		MySQL::query(static::$class)->filterOn(static::$primaryField, $primaryID)->delete();
	}

	/**
	 * Delete all records.
	 * 
	 * * Example use: User::deleteAll()
	 */
	public static function deleteAll(): void
	{
		static::invoke();

		MySQL::query(static::$class)->delete();
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
	 * Search for records.
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

		foreach ($newRecord as $field => $value)
			if (array_key_exists($field, $record))
				$record[$field] = $value;

		$clonePrimaryID = MySQL::query(static::$class)->insert($record);
		$clonedRecords = MySQL::query(static::$class)->fetch(static::$primaryField, $clonePrimaryID);

		return array_pop($clonedRecords);
	}

	/**
	 * Count the number of records.
	 * 
	 * * Example use: User::count()
	 * * Example use: User::count($filter)
	 */
	public static function count(null|array $filter = null): int
	{
		static::invoke();

		$records = [];
		if ($filter !== null)
			$records = MySQL::query(static::$class)->filterOnGroup([$filter])->select('COUNT(*) as count');
		else
			$records = MySQL::query(static::$class)->select('COUNT(*) as count');

		if (!$records)
			return 0;

		$record = array_pop($records);
		return (int) $record['count'];
	}
}
