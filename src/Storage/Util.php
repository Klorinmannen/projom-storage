<?php

declare(strict_types=1);

namespace Projom\Storage;

class Util
{
	public static function stringToList(string|array $subject, string $delimeter = ','): array
	{
		if (is_array($subject))
			return $subject;

		$subject = static::cleanString($subject);

		$list = [$subject];
		if (strpos($subject, $delimeter) !== false)
			$list = explode($delimeter, $subject);

		return $list;
	}

	public static function cleanString(string $subject): string
	{
		return str_replace(' ', '', trim($subject));
	}

	public static function cleanList(array $list): array
	{
		return array_map([self::class, 'cleanString'], $list);
	}

	public static function clean(string|array $subject): array|string
	{
		if (is_array($subject))
			return static::cleanList($subject);
		return static::cleanString($subject);
	}

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

	public static function rekey(array $records, string $field): array
	{
		return array_column($records, null, $field);
	}

	public static function format(mixed $value, string $type): mixed
	{
		$type = strtolower($type);
		return match ($type) {
			'int' => (int) $value,
			'float' => (float) $value,
			'bool' => (bool) $value,
			'date' => date('Y-m-d', strtotime((string) $value)),
			'datetime' => date('Y-m-d H:i:s', strtotime((string) $value)),
			'string' => (string) $value,
			default => $value,
		};
	}
}
