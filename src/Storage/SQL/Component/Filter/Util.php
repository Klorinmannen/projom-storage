<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Component\Filter;

use Projom\Storage\SQL\Util as SQLUtil;

class Util extends SQLUtil
{
	public static function parameterName(array $columns, int $id): string
	{
		$colString = static::join($columns, '_');
		$colString = str_replace(['.', ','], '_', $colString);
		$colString = strtolower($colString);
		return "filter_{$colString}_{$id}";
	}

	public static function addParentheses(array $filter): array
	{
		$filterCount = count($filter);

		if ($filterCount > 1) {
			array_unshift($filter, '(');
			$filter[] = ')';
		}

		return $filter;
	}
}