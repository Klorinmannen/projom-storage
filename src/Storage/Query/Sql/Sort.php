<?php

declare(strict_types=1);

namespace Projom\Storage\Query\Sql;

class Sort
{
    const ASC = 'ASC';
    const DESC = 'DESC';

    public static function format(string $sort): string
    {
        $sort = strtoupper($sort);
        switch ($sort) {
            case static::ASC:
            case static::DESC:
                return $sort;
            default:
                throw new \Exception("Internal server error: query\sql\sort::format $sort");
        }
    }
}
