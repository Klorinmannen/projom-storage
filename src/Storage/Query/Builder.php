<?php

declare(strict_types=1);

namespace Projom\Storage\Query;

use Projom\Storage\Query\Builder\Driver;

enum ActionType
{
    case SELECT;
    case UPDATE;
    case DELETE;
    case INSERT;
}

class Builder
{
    private ActionType $actionType = 0;
    private string $table = '';
    private array $columns = [];
    private array $conditions = [];
    private array $opts = [];
    private array $record = [];

    public function __construct(string $table = '')
    {
        $this->table = $table;
    }

    public function select(array|string $column = '*'): Builder
    {
        if (is_array($column))
            $this->columns = $column;
        else
            $this->columns[] = $column;
        
        $this->actionType = ActionType::SELECT;        
        return $this;
    }

    public function from(string $table): Builder
    {
        $this->table = $table;
        return $this;
    }

    public function where(
        string $column,
        string $operator,
        mixed $value,
        string $predicate = ''
    ): Builder {
        $condition = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        if ($predicate)
            $condition += [
                'predicate' => $predicate
            ];
        
        $this->conditions[] = $condition;        
        return $this;
    }

    public function or(): Builder
    {
        $condition = array_pop($this->conditions);
        $condition += [
            'predicate' => '||'
        ];
        $this->conditions[] = $condition;
        return $this;
    }

    public function and(): Builder
    {
        $condition = array_pop($this->conditions);
        $condition += [
            'predicate' => '&&'
        ];
        $this->conditions[] = $condition;
        return $this;
    }

    public function limit($limit): Builder
    {
        $this->opts['limit'] = $limit;
        return $this;
    }

    public function group(string $column): Builder
    {
        $this->opts['group'][] = $column;
        return $this;
    }

    public function sort(
        string $column,
        string $sort
    ): Builder {
        $this->opts['sort'][$column] = $sort;
        return $this;
    }

    public function custom(string $sql): Builder
    {
        $this->opts['custom'][] = $sql;
        return $this;
    }

    public function execute(): mixed
    {
        $result = [];
        switch ($this->actionType) {
            case ActionType::SELECT:
                $result = Driver::select(
                    $this->columns,
                    $this->table,
                    $this->conditions,
                    $this->opts
                );
                break;
        }

        $this->reset();
        return $result;
    }

    public function reset(): void
    {
        $this->columns = [];
        $this->conditions = [];
        $this->actionType = 0;
        $this->opts = [];
    }
}
