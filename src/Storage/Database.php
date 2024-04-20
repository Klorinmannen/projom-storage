<?php

declare(strict_types=1);

namespace Projom\Storage;

use Projom\Storage\Database\Drivers;
use Projom\Storage\Database\Engine;
use Projom\Storage\Database\Query;

class Database extends Engine
{
	protected static Drivers|null $currentDriver = null;

	public function __construct(Drivers $driver)
	{
		static::$currentDriver = $driver;
	}

	public static function create(Drivers $driver = Drivers::MySQL): Database
	{
		return new Database($driver);
	}

	public function query(string $table): Query
	{
		return static::dispatch($table);
	}

	public function sql(string $query, array|null $params = null): mixed
	{
		return static::dispatch($query, $params);
	}
}
