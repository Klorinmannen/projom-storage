<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Source;

use Projom\Storage\Database\Engine\Config;

class DSN
{
	public static function MySQL(Config $config): string
	{
		if (!$host = $config->host)
			throw new \Exception('Config is missing host', 400);
		if (!$port = $config->port)
			throw new \Exception('Config is missing port', 400);
		if (!$database = $config->database)
			throw new \Exception('Config is missing database', 400);

		$parts = [
			"host=$host",
			"port=$port",
			"dbname=$database",
		];

		if ($charset = $config->charset)
			$parts[] = "charset=$charset";

		if ($collation = $config->collation)
			$parts[] = "collation=$collation";

		return 'mysql:' . implode(';', $parts);
	}
}
