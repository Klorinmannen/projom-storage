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

    /**
     * * Example use: $query->select(Field::create(...), Filter::create(...), Filter::create(...))
     */
    public function select(Field $field, Filter ...$filters): mixed
    {
        $filter = array_shift($filters);
        $filter->merge(...$filters);

        return $this->driver->select($this->collection, $field, $filter);
    }

    /**
     * * Example use: $query->fetch('Name', 'John')
     * * Example use: $query->fetch('Age', [25, 55], Operators::IN)
     */
    public function fetch(string $field, mixed $value, Operators $operator = Operators::EQ): mixed
    {
        $fieldsWithValues = [
            $field => $value
        ];

        $field = Field::create($field);
        $filter = Filter::create($fieldsWithValues, $operator);

        return $this->driver->select($this->collection, $field, $filter);
    }

    /**
     * * Example use: $query->field('name', 'age')
     * * Example use: $query->field('name, age')
     * * Example use: $query->field([ 'name', 'age', 'username' ])
     */
    public function field(string ...$fields): Query
    {
        $this->field = Field::create(...$fields);

        return $this;
    }

    /**
     * * Example use: $query->filterOn(Operators::EQ, ['name' => 'John'])
     * * Example use: $query->filterOn(Operators::EQ, ['name' => 'John'], ['age' => 25])
     * * Example use: $query->filterOn(Operators::IN, [ 'age' => [12, 23, 45] ])
     */
    public function filterOn(Operators $operator, array ...$filters): Query
    {
        if ($this->filter === null)
            $this->filter = Filter::create(array_shift($filters), $operator);

        $newFilters = array_map(
            fn (array $filter) => Filter::create($filter, $operator),
            $filters
        );

        $this->filter->merge(...$newFilters);

        return $this;
    }

    /**
     * Executes the query and returns the result.
     */
    public function get(): mixed
    {
        return $this->driver->select($this->collection, $this->field, $this->filter);
    }
}
