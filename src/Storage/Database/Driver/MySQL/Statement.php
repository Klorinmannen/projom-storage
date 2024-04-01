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
}
