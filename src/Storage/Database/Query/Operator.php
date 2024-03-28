<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Query;

enum Operators: string
{
	case EQ = '=';
	case NE = '<>';
	case GT = '>';
	case LT = '<';
	case GTE = '>=';
	case LTE = '<=';
	case LIKE = 'LIKE';
	case NOT_LIKE = 'NOT LIKE';
	case IN = 'IN';
	case NOT_IN = 'NOT IN';
	case IS_NULL = 'IS NULL';
	case IS_NOT_NULL = 'IS NOT NULL';
    case BETWEEN = 'BETWEEN';
}

enum MathOperators: string
{
    case ADD = '+';
    case SUB = '-';
    case MUL = '*';
    case DIV = '/';
    case MOD = '%';
}

enum LogicOperators: string
{
    case AND = 'AND';
    case OR = 'OR';
}

class Operator implements AccessorInterface
{
    private Operators $raw = '';
    private string $operator = '';

    public function __construct(Operators $operator)
    {
        $this->raw = $operator;
        $this->operator = $operator->value;
    }

    public static function create(Operators $operator): Operator
    {
        return new Operator($operator);
    }
    
    public function __toString(): string 
    { 
        return $this->get();
    }
       
    public function get(): string
    {
        return $this->operator;
    }

    public function raw(): Operators
    {
        return $this->raw;
    }
}
