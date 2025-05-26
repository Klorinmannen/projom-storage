<?php

declare(strict_types=1);

namespace Projom\Storage\MySQL;

use Projom\Storage\Util as StorageUtil;

class Util extends StorageUtil
{
	public static function dynamicPrimaryField(string $calledClass, bool $useNamespaceAsTableName): string
	{
		$table = static::dynamicTableName($calledClass, $useNamespaceAsTableName);
		return $table . 'ID';
	}

	public static function dynamicTableName(string $calledClass, bool $useNamespaceAsTableName): string
	{
		if ($useNamespaceAsTableName)
			return Util::tableFromNamespace($calledClass);
		return Util::tableFromCalledClass($calledClass);
	}

	public static function tableFromNamespace(string $calledClass): string
	{
		$parts = explode('\\', $calledClass);
		array_shift($parts); // Remove the first namespace part, App .. w/e.
		array_pop($parts); // Removes the last part, which is the class name.
		return implode('', array_map('ucfirst', $parts));
	}

	public static function tableFromCalledClass(string $calledClass): string
	{
		$calledClass = str_replace('\\', DIRECTORY_SEPARATOR, $calledClass);
		$class = basename($calledClass);
		return static::replace($class, ['Repository', 'Repo']);
	}

	public static function replace(string $string, array $replace, string $replaceWith = ''): string
	{
		return str_ireplace($replace, $replaceWith, $string);
	}
}
