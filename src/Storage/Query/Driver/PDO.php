<?php

declare(strict_types=1);

namespace Projom\Storage\Query\Driver;

use Projom\Storage\Source\PDO as PDOSource;

class PDO
{
	public static function invoke(string $query, ?array $params = null): mixed
	{
		static::validateQuery($query, $params);

		$pdo = PDOSource::get();
		if (!$query = $pdo->prepare($query))
			throw new \Exception('Internal server error', 500);
		if (!$query->execute($params))
			throw new \Exception('Internal server error', 500);

		if (!$result = $query->fetchAll())
			return [];

		return $result;
	}

	private static function validateQuery(string $query, ?array $params = null): bool
	{
		// Nothing to check query string against.
		if (!$paramCount = count($params))
			return true;
		
		if ($positionalParamCount = substr_count($query, '?'))
			static::validatePositionalParams($paramCount, $positionalParamCount);
		elseif ($namedParamCount = substr_count($query, ':'))
			static::validateNamedParams($query, $params, $paramCount, $namedParamCount);
		else
			throw new \Exception('Query string does not match provided parameters', 400);

	}

	private static function validatePositionalParams(int $paramCount, int $positionalParamCount): void
	{
		// Example.
		// $params = [ 1, 'John'];
		// $query = 'SELECT * FROM users WHERE id = ? AND name = ?';

		if ($paramCount !== $positionalParamCount)
			throw new \Exception('Parameter count does not match the number of positional parameters', 400);
	}

	private static function validateNamedParams(string $query, array $params, int $paramCount, int $namedParamCount): void
	{		
		if ($paramCount !== $namedParamCount)
			throw new \Exception('Parameter count does not match the number of provided named parameters', 400);

		// Example.
		// $params = ['id' => 1, 'name' => 'John'];
		// $query = 'SELECT * FROM users WHERE id = :id AND name = :name';

		$paramNames = array_map(
			function(string $case) { 
				return strpos($case, ':') === false ? ':' . $case : $case; 
			}, 
			array_keys($params)
		);

		$regexString = '/' . implode('|', $paramNames) . '/';

		if (!$paramCount === preg_match_all($regexString, $query, $matches))
			throw new \Exception('Parameter count does not match with provided named parameter query string', 400);
	}
}