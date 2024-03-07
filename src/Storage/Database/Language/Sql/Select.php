<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\Sql;

use Projom\Storage\Database\Language\Sql\Column;

class Select
{
    public static function build(array|string $columnList): string
    {
        if (is_string($columnList))
            $columnList = [ $columnList ];

        $quotedColumnList = array_map(
            [ Column::class, 'quote'],
            $columnList
        );
        $quotedString = Column::join($quotedColumnList);
        return "SELECT $quotedString";
    }
}
