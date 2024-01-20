<?php

declare(strict_types=1);

namespace Projom\Storage\Query\Sql;

class Limit
{
    public static function format(mixed $limit): int
    {
        if (!is_numeric($limit))
            throw new \Exception("Internal server error: query\sql\limit::format $limit");

        return (int)$limit;
    }
}
