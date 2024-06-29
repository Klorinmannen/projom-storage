<?php

namespace Projom\Storage\Database\Query;

enum LogicalOperator: string
{
	case AND = 'AND';
	case OR = 'OR';
}
