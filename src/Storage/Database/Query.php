<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Query\Constraint;
use Projom\Storage\Database\Query\Constraint\Equals;
use Projom\Storage\Database\Query\Constraint\NotEquals;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Collection;
use Projom\Storage\Database\QueryInterface;

class Query implements QueryInterface
{
    private DriverInterface|null $driver = null;
    private Collection $collection = '';

    private array $constraints = [];

    public function __construct(DriverInterface $driver, string $collection)
    {
        $this->driver = $driver;
        $this->collection = Collection::create($collection);
    }

    public function select(Field $field, Constraint ...$constraints): mixed
    {
        return $this->driver->select($this->collection, $field, $constraints);
    }

    public function fetch(string $field, mixed $value): mixed
    {
        $fieldsWithValues = [
            $field => $value
        ];

        $field = Field::create($field);
        $constraint = Equals::create($fieldsWithValues);

        return $this->driver->select($this->collection, $field, [ $constraint ]);
    }

    public function eq(array ...$constraints): mixed
    {
        $newConstraints = array_map(
            fn (array $constraint) => Equals::create($constraint),
            $constraints
        );

        $this->constraints = [ ...$this->constraints, ...$newConstraints ];

        return $this;
    }

    public function ne(array ...$constraints): mixed
    {
        $newConstraints = array_map(
            fn (array $constraint) => NotEquals::create($constraint),
            $constraints
        );

        $this->constraints = [ ...$this->constraints, ...$newConstraints ];

        return $this;
    }

    public function get(): mixed
    {
        return $this->driver->select($this->collection, ...$this->constraints);
    }
}
