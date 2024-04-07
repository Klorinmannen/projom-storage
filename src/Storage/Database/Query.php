<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Query\Filter;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Collection;
use Projom\Storage\Database\Query\LogicalOperators;
use Projom\Storage\Database\Query\Operators;

class Query
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
        $filter = Filter::create($operator, $fieldsWithValues);

        return $this->driver->select($this->collection, $field, $filter);
    }

    /**
     * * Example use: $query->filterOn(Operators::EQ, ['Name' => 'John'])
     * * Example use: $query->filterOn(Operators::NE, ['Name' => 'John'], ['Age' => 25])
     * * Example use: $query->filterOn(Operators::IN, [ 'Age' => [12, 23, 45] ])
     */
    public function filterOn(
        Operators $operator,
        array $fieldsWithValues,
        LogicalOperators $logicalOperators = LogicalOperators::AND
    ): Query {

        $filter = Filter::create($operator, $fieldsWithValues, $logicalOperators);
        if ($this->filter === null)
            $this->filter = $filter;
        else
            $this->filter->merge($filter);

        return $this;
    }

    /**
     * Executes a select query and returns the result.
     * 
     * * Example use: $query->get('Name', 'Age')
     * * Example use: $query->get('Name, Age')
     * * Example use: $query->get([ 'Name', 'Age', 'Username' ])
     */
    public function get(string ...$fields): mixed
    {
        $this->field = Field::create(...$fields);
        return $this->driver->select($this->collection, $this->field, $this->filter);
    }
}
