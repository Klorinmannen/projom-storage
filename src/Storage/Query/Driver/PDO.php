<?php

declare(strict_types=1);

namespace Projom\Storage\Query\Driver;

use Projom\Storage\Source\PDO as Source;

class PDO
{
	public static function invoke(string $query, ?array $params = null): mixed
	{
		// Do further controlls on the query and params
		// If the query string matches the $params array, then execute the query
		
		// Example 1:
		// $params = ['id' => 1, 'name' => 'John'];
		// $query = 'SELECT * FROM users WHERE id = :id AND name = :name';

		// Example 2:
		// $params = [ 1, 'John'];
		// $query = 'SELECT * FROM users WHERE id = ? AND name = ?';

		$pdo = Source::get();

		if (!$query = $pdo->prepare($query))
			throw new \Exception('Internal server error', 500);
		if (!$query->execute($params))
			throw new \Exception('Internal server error', 500);

		if (!$result = $query->fetchAll())
			return [];

		return $result;
	}
}