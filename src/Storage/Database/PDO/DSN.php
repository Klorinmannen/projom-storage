<?php

declare(strict_types=1);

namespace Projom\Storage\Database\PDO;

use Exception;

trait DSN
{
    public static function DSN(array $config): string
    {
        [$driver, $host, $port, $dbname, $charset] = static::parseConfig($config);
        
		if (!$driver)
			throw new Exception('Missing DNS driver', 400);
		if (!$host)
			throw new Exception('Missing DNS server host', 400);
		if (!$port)
			throw new Exception('Missing DNS server port', 400);
		if (!$dbname)
			throw new Exception('Missing DNS database name', 400);

		$parts = [
			"host=$host",
			"port=$port",
			"dbname=$dbname",
		];

		if ($charset)
			$parts[] = "charset=$charset";

		return "$driver:" . implode(';', $parts);
    }

	public static function parseConfig($config) 
	{
		return [
			$config['driver'] ?? '',
			$config['host'] ?? '',
			$config['port'] ?? '',
			$config['dbname'] ?? '',
			$config['charset'] ?? ''
		];	
	}
}
