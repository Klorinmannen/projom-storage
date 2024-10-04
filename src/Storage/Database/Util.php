<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

class Util
{
	public static function join(array $list, string $delimeter = ','): string
	{
		return implode($delimeter, $list);
	}

	public static function split(string $subject, string $delimeter = ','): array
	{
		return explode($delimeter, $subject);
	}

	public static function flatten(array $list): array
	{
		return array_merge(...$list);
	}

	public static function merge(array ...$lists): array
	{
		return array_merge(...$lists);
	}

	public static function removeEmpty(array $list): array
	{
		return array_filter($list);
	}

	public static function match(string $pattern, string $subject): array
	{
		if (preg_match($pattern, $subject, $matches) === 1)
			return $matches;

		return [];
	}

	public static function is_int(string|int|float $subject): bool
	{
		$subject = (string) $subject;
		return is_numeric($subject) && strpos($subject, '.') === false;
	}
}
