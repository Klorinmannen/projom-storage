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

    public static function update(Table $table, array $fieldsWithValues, Filter $filter): array
    {
        $id = 0;
        $params = $filter->params() ?? [];
        $updateFields = [];
        foreach ($fieldsWithValues as $field => $value) {
            $id++;
            $qoutedField = Util::quote($field);
            $setField = strtolower("set_{$field}_{$id}");
            $updateFields[] = "{$qoutedField} = :{$setField}";
            $params[$setField] = $value;
        }      

        $updateList = Util::join($updateFields, ', ');

        $query = match ($filter->empty()) {
            false => "UPDATE {$table} SET {$updateList} WHERE {$filter}",
            default => "UPDATE {$table} SET {$updateList}"
        };

        return [
            $query,
            $params
        ];
    }

    public static function update(Table $table, array $fieldsWithValues, Filter $filter): array
    {
        $id = 0;
        $params = $filter->params() ?? [];
        $updateFields = [];
        foreach ($fieldsWithValues as $field => $value) {
            $id++;
            $qoutedField = Util::quote($field);
            $setField = strtolower("set_{$field}_{$id}");
            $updateFields[] = "{$qoutedField} = :{$setField}";
            $params[$setField] = $value;
        }      

        $updateList = Util::join($updateFields, ', ');

        $query = match ($filter->empty()) {
            false => "UPDATE {$table} SET {$updateList} WHERE {$filter}",
            default => "UPDATE {$table} SET {$updateList}"
        };

        return [
            $query,
            $params
        ];
    }
}
