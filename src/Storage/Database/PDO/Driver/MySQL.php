<?php

declare(strict_types=1);

namespace Projom\Storage\Database\PDO\Driver;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Language\SQL;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\PDO\Source;
use Projom\Storage\Database\Query\Collection;
use Projom\Storage\Database\Query\Field;

class MySQL implements DriverInterface
{
	use Source, SQL;

	public function __construct(array $config)
	{
		$this->connect($config);
	}

	public function select(Collection $collection, Field $field, array $constraints): mixed
	{
		$query = '';
		$params = [];
		
		return $this->execute($query, $params);
	}

	public function Query(string $table): Query
	{
		return new Query($this, $table);
	}

	public static function create(array $config): MySQL
	{
		return new MySQL($config);
	}
}
