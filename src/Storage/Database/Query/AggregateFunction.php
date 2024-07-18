<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

enum AggregateFunction: string 
{
	case COUNT = 'COUNT';
	case MIN = 'MIN';
	case MAX = 'MAX';
	case AVG = 'AVG';
	case SUM = 'SUM';
}