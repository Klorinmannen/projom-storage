<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language;

use Projom\Storage\Database\Language\Sql\Operator;

trait SQL
{
	public static function selectQueryWithParams(string $table, array $constraint): array
	{	
		return [];
	}
}