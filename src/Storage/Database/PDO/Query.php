<?php

declare(strict_types=1);

namespace Projom\Storage\Database\PDO;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Language\Sql\Operator;
use Projom\Storage\Database\QueryInterface;

class Query implements QueryInterface
{
    private DriverInterface $driver;
    private string $table;

    public function __construct(DriverInterface $driver, string $table)
    {
        $this->driver = $driver;
        $this->table = $table;
    }

    public function select(string $column = '*', mixed $value = []): mixed
    {
        return $this->driver->select($this->table, $column, $value, Operator::EQ);
    }
    
	public function sql(string $query, ?array $params = null): mixed
	{
		return $this->driver->execute($query, $params);
	}
}
