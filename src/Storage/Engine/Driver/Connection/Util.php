<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver\Connection;

use Projom\Storage\Util as StorageUtil;

class Util extends StorageUtil
{
	public static function fileExists(string $filePath): bool
	{
		if (! file_exists($filePath))
			return false;
		if (! is_file($filePath))
			return false;
		if (! is_readable($filePath))
			return false;
		return true;
	}
}
