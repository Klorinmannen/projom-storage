<?php
declare(strict_types=1);

namespace Projom\Storage\Database\Query;

enum LogicOperators: string
{
    case AND = 'AND';
    case OR = 'OR';
}
