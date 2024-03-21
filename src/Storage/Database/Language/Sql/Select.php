<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\Sql;

use Projom\Storage\Database\Language\Sql\Column;

class Select
{
    public static function build(array|string $columnList): string
    {
        if (!is_array($columnList))
            $columnList = [ $columnList ];

        $quotedFields = array_map(
            [ Select::class, 'quote'],
            $columnList
        );

        $quotedString = static::join($quotedFields);

        return "SELECT $quotedString";
    }

    public static function quote(string $column): string
    {
        return "`$column`";
    }

    public static function join(array $quotedFields): string
    {
        return implode(', ', $quotedFields);
    }
}
