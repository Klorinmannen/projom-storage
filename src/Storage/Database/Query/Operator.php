<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

enum Operator: string
{
	case EQ = '=';
	case NE = '<>';
	case GT = '>';
	case LT = '<';
	case GTE = '>=';
	case LTE = '<=';
	case IS_NULL = 'IS NULL';
	case IS_NOT_NULL = 'IS NOT NULL';
	case IN = 'IN';
	case NOT_IN = 'NOT IN';
	case LIKE = 'LIKE';
	case NOT_LIKE = 'NOT LIKE';
}
