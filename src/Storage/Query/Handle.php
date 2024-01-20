<?php

declare(strict_types=1);

namespace Projom\Storage\Query;

use PDO;
use Projom\Storage\Query\Sql\Condition;
use Projom\Storage\Query\Sql\Util;
use Projom\Storage\Query\Sql\Select;
use Projom\Storage\Query\Sql\Where;

class Handle
{
    private $pdo = null;
    private $statement = '';
    private $params = null;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function select(
        array $columnList,
        string $table,
        array $conditionList,
        array $opts
    ): array {
        $select = Select::build($columnList);
        $table = Util::quote($table);

        $sqlConditionList = Condition::buildList($conditionList);
        $where = Where::build($sqlConditionList);

        $this->statement = "$select FROM $table $where";
        $this->params = Where::namedParameterList($sqlConditionList);

        if (!$query = $this->pdo->prepare($this->statement))
            throw new \Exception("Internal server error: query\dbh\pdo::statement prepare {$this->statement}", 500);
        if (!$query->execute($this->params))
            throw new \Exception("Internal server error: query\dbh\pdo::statement execute {$this->statement}", 500);

        if (!$result = $query->fetchAll())
            return [];

        return $result;
    }
}
