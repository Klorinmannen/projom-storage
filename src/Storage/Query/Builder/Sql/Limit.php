<?php

declare(strict_types=1);

namespace Projom\Storage\Query\Builder\Sql;

class Limit
{
    public static function format(mixed $limit): int
    {
        if (!is_numeric($limit))
            throw new \Exception("Internal server error: sql\limit::format $limit");

        return (int)$limit;
    }
}
