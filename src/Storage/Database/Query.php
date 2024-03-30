<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\QueryInterface;
use Projom\Storage\Database\Query\Filter;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Collection;
use Projom\Storage\Database\Query\Operators;

class Query implements QueryInterface
{
    private DriverInterface|null $driver = null;
    private Collection|null $collection = null;
    private Field|null $field = null;
    private Filter|null $filter = null;

    public function __construct(DriverInterface $driver, string $collection)
    {
        $this->driver = $driver;
        $this->collection = Collection::create($collection);
    }

    public function select(Field $field, Filter ...$filters): mixed
    {
        $filter = array_shift($filters);
        $filter->merge(...$filters);

        return $this->driver->select($this->collection, $field, $filter);
    }

    public function fetch(string $field, mixed $value, Operators $operator = Operators::EQ): mixed
    {
        $fieldsWithValues = [
            $field => $value
        ];

        $field = Field::create($field);
        $filter = Filter::create($fieldsWithValues, $operator);

        return $this->driver->select($this->collection, $field, $filter);
    }

    public function field(string ...$fields): Query
    {
        $this->field = Field::create(...$fields);

        return $this;
    }

    public function eq(array ...$filters): Query
    {
        if ($this->filter === null)
            $this->filter = Filter::create(array_shift($filters), Operators::EQ);

            $newFilters = array_map(
            fn (array $filter) => Filter::create($filter, Operators::EQ),
            $filters
        );

        $this->filter->merge(...$newFilters);

        return $this;
    }

    public function ne(array ...$filters): Query
    {
        if ($this->filter === null)
            $this->filter = Filter::create(array_shift($filters), Operators::NE);

        $newFilters = array_map(
            fn (array $filter) => Filter::create($filter, Operators::NE),
            $filters
        );

        $this->filter->merge(...$newFilters);

        return $this;
    }

    public function get(): mixed
    {
        return $this->driver->select($this->collection, $this->field, $this->filter);
    }
}
