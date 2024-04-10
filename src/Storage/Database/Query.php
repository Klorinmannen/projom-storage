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
    private Filter|null $filter = null;

    public function __construct(DriverInterface $driver, string $collection)
    {
        $this->driver = $driver;
        $this->collection = Collection::create($collection);
    }

    /**
     * Simple query mechanism to find a record by a field and its value.
     * 
     * * Example use: $database->query('CollectionName')->fetch('Name', 'John')
     * * Example use: $database->query('CollectionName')->fetch('Age', [25, 55], Operators::IN)
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
     * Creating a filter to be used in the query to be executed.
     * 
     * * Example use: $database->query('CollectionName')->filterOn(Operators::EQ, ['Name' => 'John'])
     * * Example use: $database->query('CollectionName')->filterOn(Operators::NE, ['Name' => 'John'], ['Age' => 25])
     * * Example use: $database->query('CollectionName')->filterOn(Operators::IN, [ 'Age' => [12, 23, 45] ])
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
     * Executes a query finding a record and returns the result.
     * 
     * * Example use: $database->query('CollectionName')->get('Name', 'Age')
     * * Example use: $database->query('CollectionName')->get('Name, Age')
     * * Example use: $database->query('CollectionName')->get([ 'Name', 'Age', 'Username' ])
     */
    public function get(string ...$fields): mixed
    {
        $field = Field::create(...$fields);
        return $this->driver->select($this->collection, $field, $this->filter);
    }

    /**
     * Executes a query modifying record(s) and returns the number of affected rows.
     * 
     * * Example use: $database->query('CollectionName')->modify(['Name' => 'John', 'Age' => 25])
     */
    public function modify(array $fieldsWithValues): int
    {
        return $this->driver->update($this->collection, $fieldsWithValues, $this->filter);
    }

    /**
     * Executing a query adding a record and returns the latest inserted primary id.
     * 
     * * Example use: $database->query('CollectionName')->add(['Name' => 'John', 'Age' => 25])
     */
    public function add(array $fieldsWithValues): int
    {
        return $this->driver->insert($this->collection, $fieldsWithValues);
    }
}
