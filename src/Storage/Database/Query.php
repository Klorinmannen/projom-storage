<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Query\Constraint;
use Projom\Storage\Database\QueryInterface;

class Query implements QueryInterface
{
    private DriverInterface|null $driver = null;
    private string $table = '';

    private array $constraints = [];

    public function __construct(DriverInterface $driver, string $table)
    {
        $this->driver = $driver;
        $this->table = $table;
    }

    public function select(Constraint ...$constraints): mixed
    {
        return $this->driver->select($this->table, $constraints);
    }

    public function fetch(string $field, mixed $value): mixed
    {
        $fieldsWithValues = [
            $field => $value
        ];

        $constraint = Constraint::create($fieldsWithValues)->eq();

        return $this->driver->select($this->table, [$constraint]);
    }

    public function eq(array ...$constraints): mixed
    {
        $newConstraints = array_map(
            fn (array $constraint) => Constraint::create($constraint)->eq(),
            $constraints
        );

        $this->constraints = [ ...$this->constraints, ...$newConstraints ];
    }

    public function ne(array ...$constraints): mixed
    {
        $newConstraints = array_map(
            fn (array $constraint) => Constraint::create($constraint)->ne(),
            $constraints
        );

        $this->constraints = [ ...$this->constraints, ...$newConstraints ];
    }

    public function get(): mixed
    {
        return $this->driver->select($this->table, ...$this->constraints);
    }
}
