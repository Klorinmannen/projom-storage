<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

enum Values
{
	case STRING;
	case BOOL;
	case NUMERIC;
	case NULL;
	case ARRAY;
	case NONE;
}
