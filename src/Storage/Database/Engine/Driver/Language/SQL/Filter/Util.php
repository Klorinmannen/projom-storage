<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language\SQL\Filter;

use Projom\Storage\Database\Engine\Driver\Language\Util as LanguageUtil;

class Util extends LanguageUtil
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