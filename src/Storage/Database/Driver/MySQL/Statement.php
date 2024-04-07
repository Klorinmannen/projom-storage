<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver\MySQL;

use Projom\Storage\Database\Driver\MySQL\Column;
use Projom\Storage\Database\Driver\MySQL\Filter;
use Projom\Storage\Database\Driver\MySQL\Table;

class Statement
{
    private Table $table;
    private Column $column;
    private Filter $filter;

    public function __construct(Table $table, Column $column, Filter $filter)
    {
        $this->table = $table;
        $this->column = $column;
        $this->filter = $filter;
    }

    public static function create(Table $table, Column $column, Filter $filter): Statement
    {
        return new Statement($table, $column, $filter);
    }

    public function select(): array
    {
        
        $query = match ($this->filter->empty()) {
            false => "SELECT {$this->column} FROM {$this->table} WHERE {$this->filter}",
            default => "SELECT {$this->column} FROM {$this->table}"
        };

        return [ $query, $this->filter->params() ];
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
