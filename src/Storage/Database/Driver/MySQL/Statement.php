<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Driver\MySQL\Column;
use Projom\Storage\Database\Driver\MySQL\Filter;
use Projom\Storage\Database\Driver\MySQL\Set;
use Projom\Storage\Database\Driver\MySQL\Sort;
use Projom\Storage\Database\Driver\MySQL\Table;

class Statement
{
    public static function select(Table $table, Column $column, Filter $filter, Sort $sort): array
    {
        $query = "SELECT {$column} FROM {$table}";

        if (!$filter->empty()) 
            $query .= " WHERE {$filter}";

        if (!$sort->empty())
            $query .= " ORDER BY {$sort}";

        return [
            $query,
            $filter->params() ?: null
        ];
    }

    public static function update(Table $table, Set $set, Filter $filter): array
    {
        $query = match ($filter->empty()) {
            false => "UPDATE {$table} SET {$set} WHERE {$filter}",
            default => "UPDATE {$table} SET {$set}"
        };

        return [
            $query,
            $set->params() + $filter->params() ?: null
        ];
    }

    public static function insert(Table $table, Set $set): array
    {
        $fieldString = $set->fieldString();
        $positionalString = $set->positionalString();

        $query = "INSERT INTO {$table} ({$fieldString}) VALUES ({$positionalString})";

        return [
            $query,
            $set->positionalParams() ?: null
        ];
    }

    public static function delete(Table $table, Filter $filter): array
    {
        $query = "DELETE FROM {$table} WHERE {$filter}";

        return [
            $query,
            $filter->params() ?: null
        ];
    }
}
