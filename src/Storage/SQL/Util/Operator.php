<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Util;

use Stringable;

enum Operator: string implements Stringable
{
	case EQ = '=';
	case NE = '<>';
	case GT = '>';
	case GTE = '>=';
	case LT = '<';
	case LTE = '<=';
	case LIKE = 'LIKE';
	case NOT_LIKE = 'NOT LIKE';
	case IN = 'IN';
	case NOT_IN = 'NOT IN';
	case IS_NULL = 'IS NULL';
	case IS_NOT_NULL = 'IS NOT NULL';
	case BETWEEN = 'BETWEEN';
	case NOT_BETWEEN = 'NOT BETWEEN';

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, static::cases());
	}

	public function __toString(): string
	{
		return $this->name;
	}
}
