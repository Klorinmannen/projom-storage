<?php

declare(strict_types=1);

namespace Projom\Storage\Query\Sql;

class Operator
{
    const EQ = '=';
    const NE = '<>';
    const LT = '<';
    const LTE = '<=';
    const GT = '>';
    const GTE = '>=';
    const IN = 'IN';

    public static function format(string $operator): string
    {
        $operator = strtolower($operator);
        switch ($operator) {
            case static::EQ:
            case static::NE:
            case static::LT:
            case static::LTE:
            case static::GT:
            case static::GTE:
            case static::IN:
                return $operator;
            case 'eq':
                return static::EQ;
            case 'ne':
                return static::NE;
            case 'lt':
                return static::LT;
            case 'lte':
                return static::LTE;
            case 'gt':
                return static::GT;
            case 'gte':
                return static::GTE;
            case 'in':
                return static::IN;
            default:
                throw new \Exception("Internal server error: query\sql\operator::format $operator");
        }
    }
}
