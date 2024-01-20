<?php

declare(strict_types=1);

namespace Projom\Storage\Query\Sql;

class Predicate
{
    const OR = 'OR';
    const AND = 'AND';

    public static function format(string $predicate): string
    {
        $predicate = strtoupper($predicate);
        switch ($predicate) {
            case static::OR:
            case static::AND:
                return $predicate;
            case '||':
            case '|':
                return static::OR;
            case '&&':
            case '&':
                return static::AND;
            default:
                throw new \Exception("Internal server error: query\sql\predicate::format $predicate");
        }
    }
}
