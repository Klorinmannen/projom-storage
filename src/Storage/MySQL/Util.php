<?php

declare(strict_types=1);

namespace Projom\Storage\MySQL;

use Projom\Storage\Util as StorageUtil;

class Util extends StorageUtil
{
	public static function classFromCalledClass(string $calledClass): string
	{
		$calledClass = str_replace('\\', DIRECTORY_SEPARATOR, $calledClass);
		$class = basename($calledClass);
		return $class;
	}

	public static function replace(string $string, array $replace, string $replaceWith = ''): string
	{
		return str_ireplace($replace, $replaceWith, $string);
	}
}
