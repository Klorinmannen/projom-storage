<?php

declare(strict_types=1);

namespace Projom\Storage;

use PDO;
use Projom\Storage\Query\Handle;

enum ActionType
{
    case SELECT;
    case UPDATE;
    case DELETE;
    case INSERT;
}

class Connection
{
    private $handle = null;
    private $actionType = 0;
    private $table = '';
    private $columnList = [];
    private $conditionList = [];
    private $opts = [];

    public function __construct(PDO $pdo)
    {
        $this->handle = new Handle($pdo);
    }

    public function setFrom(string $table): void
    {
        $this->from($table);
    }

    public function selectList(array $columnList): Connection
    {
        $this->columnList = $columnList;
        $this->actionType = ActionType::SELECT;
        return $this;
    }

    public function selectAll(): Connection
    {
        $this->select('*');
        return $this;
    }

    public function select(string $column): Connection
    {
        $this->columnList[] = $column;
        $this->actionType = ActionType::SELECT;
        return $this;
    }

    public function from(string $table): Connection
    {
        $this->table = $table;
        return $this;
    }

    public function where(
        string $column,
        string $operator,
        mixed $value,
        string $predicate = ''
    ): Connection {
        $condition = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        if ($predicate)
            $condition += [
                'predicate' => $predicate
            ];

        $this->conditionList[] = $condition;
        return $this;
    }

    public function or(): Connection
    {
        $condition = array_pop($this->conditionList);
        $condition += [
            'predicate' => '||'
        ];
        $this->conditionList[] = $condition;
        return $this;
    }

    public function and(): Connection
    {
        $condition = array_pop($this->conditionList);
        $condition += [
            'predicate' => '&&'
        ];
        $this->conditionList[] = $condition;
        return $this;
    }

    public function limit($limit): Connection
    {
        $this->opts['limit'] = $limit;
        return $this;
    }

    public function group(string $column): Connection
    {
        $this->opts['group'][] = $column;
        return $this;
    }

    public function sort(
        string $column,
        string $sort
    ): Connection {
        $this->opts['sort'][$column] = $sort;
        return $this;
    }

    public function sql(string $sql): Connection
    {
        $this->opts['sql'][] = $sql;
        return $this;
    }

    public function insert(array $record): Connection
    {
        $this->actionType = ActionType::INSERT;
        $this->columnList = $record;
        return $this;
    }

    public function update(array $record): Connection
    {
        $this->actionType = ActionType::UPDATE;
        $this->columnList = $record;
        return $this;
    }

    public function delete(int $id): Connection
    {
        $this->actionType = ActionType::DELETE;
        $this->columnList = ['id' => $id];
        return $this;
    }

    public function execute(): array
    {
        $result = [];
        switch ($this->actionType) {
            case ActionType::SELECT:
                $result = $this->handle->select(
                    $this->columnList,
                    $this->table,
                    $this->conditionList,
                    $this->opts
                );
                break;
            case ActionType::UPDATE:
                break;
            case ActionType::INSERT:
                break;
            case ActionType::DELETE:
                break;
        }

        $this->reset();
        return $result;
    }

    public function reset(): void
    {
        $this->columnList = [];
        $this->conditionList = [];
        $this->actionType = 0;
        $this->opts = [];
    }
}
