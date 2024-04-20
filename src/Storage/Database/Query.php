<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Query\Filter;
use Projom\Storage\Database\Query\Field;
use Projom\Storage\Database\Query\Collection;
use Projom\Storage\Database\Query\LogicalOperators;
use Projom\Storage\Database\Query\Operators;
use Projom\Storage\Database\Query\Sort;
use Projom\Storage\Database\Query\Value;

class Query
{
    private DriverInterface|null $driver = null;
    private Collection|null $collection = null;
    private Filter|null $filter = null;

    public function __construct(DriverInterface $driver, string $collection)
    {
        $this->driver = $driver;
        $this->collection = Collection::create($collection);
        $this->filter = Filter::create([], Operators::EQ);
    }

    public static function create(DriverInterface $driver, string $collection): Query
    {
        return new Query($driver, $collection);
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
        $filter = Filter::create($fieldsWithValues, $operator);
        $this->filter->merge($filter);

        return $this->driver->select($this->collection, $field, $this->filter);
    }

    /**
     * Creating a filter to be used in the query to be executed.
     * 
     * * Example use: $database->query('CollectionName')->filterOn(['Name' => 'John'])
     * * Example use: $database->query('CollectionName')->filterOn(['Name' => 'John'], ['Age' => 25], Operators::NE)
     * * Example use: $database->query('CollectionName')->filterOn([ 'Age' => [12, 23, 45] ], Operators::IN)
     */
    public function filterOn(
        array $fieldsWithValues,
        Operators $operator = Operators::EQ,
        LogicalOperators $logicalOperators = LogicalOperators::AND
    ): Query {

        $filter = Filter::create($fieldsWithValues, $operator, $logicalOperators);
        $this->filter->merge($filter);

        return $this;
    }

    /**
     * Executes a query finding record(s) and returns the result.
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
        $value = Value::create($fieldsWithValues);
        return $this->driver->update($this->collection, $value, $this->filter);
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
        $value = Value::create($fieldsWithValues);
        return $this->driver->insert($this->collection, $value);
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

    /**
     * Sorts the result of the query.
     * 
     * * Example use: $database->query('CollectionName')->get(['UserID', 'Username'])->sort('Username', Sort::ASC)
     */
    public function sort(string $field, Sort $sort): Query
    {
        return $this;
    }
}
