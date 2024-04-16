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
        $this->filter = Filter::create(Operators::EQ, []);
    }

    /**
     * Simple query mechanism to find record(s) by a field and its value.
     * 
     * * Example use: $database->query('CollectionName')->fetch('Name', 'John')
     * * Example use: $database->query('CollectionName')->fetch('Age', [25, 55], Operators::IN)
     */
    public function fetch(string $field, mixed $value, Operators $operator = Operators::EQ): array
    {
        $fieldsWithValues = [
            $field => $value
        ];

        $field = Field::create($field);
        $filter = Filter::create($operator, $fieldsWithValues);
        $this->filter->merge($filter);

        return $this->driver->select($this->collection, $field, $this->filter);
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
        $this->filter->merge($filter);

        return $this;
    }

    /**
     * Executes a query finding a record and returns the result.
     * 
     * * Example use: $database->query('CollectionName')->get('*')
     * * Example use: $database->query('CollectionName')->get('Name', 'Age')
     * * Example use: $database->query('CollectionName')->get('Name, Age')
     * * Example use: $database->query('CollectionName')->get([ 'Name', 'Age', 'Username' ])
     */
    public function get(string ...$fields): array
    {
        $field = Field::create(...$fields);
        return $this->driver->select($this->collection, $field, $this->filter);
    }
    /**
     * Alias for get method.
     */
    public function select(string ...$fields): array
    {
        return $this->get(...$fields);
    }

    /**
     * Executes a query modifying record(s) and returns the number of affected rows.
     * 
     * * Example use: $database->query('CollectionName')->modify(['Active' => 1])
     * * Example use: $database->query('CollectionName')->filterOn( ... )->modify(['Username' => 'Jane', 'Password' => 'password'])
     */
    public function modify(array $fieldsWithValues): int
    {
        return $this->driver->update($this->collection, $fieldsWithValues, $this->filter);
    }
    /**
     * Alias for modify method.
     */
    public function update(array $fieldsWithValues): int
    {
        return $this->modify($fieldsWithValues);
    }

    /**
     * Executes a query adding a record and returns the latest inserted primary id.
     * 
     * * Example use: $database->query('CollectionName')->add(['Username' => 'John', 'Password' => '1234'])
     */
    public function add(array $fieldsWithValues): int
    {
        return $this->driver->insert($this->collection, $fieldsWithValues);
    }

    /**
     * Alias for add method.
     */
    public function insert(array $fieldsWithValues): int
    {
        return $this->add($fieldsWithValues);
    }

    /**
     * Executes a query removing record(s) and returns the number of affected rows.
     * 
     * * Example use: $database->query('CollectionName')->filterOn( ... )->remove()
     */
    public function remove(): int
    {
        return $this->driver->delete($this->collection, $this->filter);
    }

    /**
     * Alias for remove method.
     */
    public function delete(): int
    {
        return $this->remove();
    }
}
