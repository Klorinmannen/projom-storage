<?php

declare(strict_types=1);

namespace Projom\Storage\MySQL;

use Projom\Storage\MySQL;
use Projom\Storage\SQL\Util\Operator;

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

	protected static function find(string|int $primaryID): array
	{
		static::invoke();

		$records = MySQL::query(static::$class)->fetch(static::$primaryField, $primaryID);

		return $records;
	}

	protected static function all(): array
	{
		static::invoke();

		$records = MySQL::query(static::$class)->select();

		return $records;
	}

	protected static function get(string $field, mixed $value): array
	{
		static::invoke();

		$records = MySQL::query(static::$class)->fetch($field, $value);

		return $records;
	}

	protected static function update(string|int $primaryID, array $data): void
	{
		static::invoke();

		MySQL::query(static::$class)->filterOn(static::$primaryField, $primaryID)->update($data);
	}

	protected static function delete(string|int $primaryID): void
	{
		static::invoke();

		MySQL::query(static::$class)->filterOn(static::$primaryField, $primaryID)->delete();
	}

	protected static function create(array $record): void
	{
		static::invoke();

		MySQL::query(static::$class)->insert($record);
	}

	protected static function search(string $field, mixed $value): array
	{
		static::invoke();

		$records = MySQL::query(static::$class)->filterOn($field, $value, Operator::LIKE)->select();

		return $records;
	}

	protected static function clone(string|int $primaryID): array
	{
		static::invoke();

		$record = MySQL::query(static::$class)->fetch(static::$primaryField, $primaryID);
		unset($record[static::$primaryField]);

		$id = MySQL::query(static::$class)->insert($record);
		$record = MySQL::query(static::$class)->fetch(static::$primaryField, $id);

		return $record;
	}

	public static function count(string $field, mixed $value): int
	{
		static::invoke();

		$records = MySQL::query(static::$class)->filterOn($field, $value)->select('COUNT(*) as count');
		$record = array_pop($records);
		if ($record === null)
			return 0;

		return (int) $record['count'];
	}
}
