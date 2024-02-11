<?php

declare(strict_types=1);

namespace Projom\Storage\Query\Builder\Sql;

use Projom\Storage\Query\Builder\Sql\Util;

class Select
{
    public static function build(array $columnList): string
    {
        $quotedColumnList = array_map(
            [self::class, 'quoteColumn'],
            $columnList
        );
        $quotedString = Util::join($quotedColumnList);
        return "SELECT $quotedString";
    }

    public static function quoteColumn(string $column): string
    {
        return Util::quote($column);
    }
}
