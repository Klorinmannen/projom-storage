<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Driver\MySQL\Column;
use Projom\Storage\Database\Driver\MySQL\Filter;
use Projom\Storage\Database\Driver\MySQL\Table;

class Statement
{
    public static function select(Table $table, Column $column, Filter $filter): array
    {
        $query = match ($filter->empty()) {
            false => "SELECT {$column} FROM {$table} WHERE {$filter}",
            default => "SELECT {$column} FROM {$table}"
        };

        return [
            $query,
            $filter->params()
        ];
    }
}
