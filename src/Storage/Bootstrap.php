<?php

declare(strict_types=1);

namespace Projom\Storage;

use Projom\Storage\Source\PDO;
use Projom\Util\File;

class Bootstrap
{
	public static function start(string $fullConfigFilePath): void
	{
		if (!File::isReadable($fullConfigFilePath))
			throw new \Exception('Configuration file is not readable.', 500);

		if (!$config = File::parse($fullConfigFilePath))
			throw new \Exception('Configuration file is not valid.', 500);

		PDO::set($config);
	}
}