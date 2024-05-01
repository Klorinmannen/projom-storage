<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\LogicalOperators;
use Projom\Storage\Database\Operators;

class Query
{
    private DriverInterface|null $driver = null;

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public static function create(DriverInterface $driver): Query
    {
        return new Query($driver);
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

        $field = [$field];
        $this->driver->setField($field);
        $this->driver->setFilter($fieldsWithValues, $operator, LogicalOperators::AND);

        return $this->driver->select();
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
        $this->driver->setFilter($fieldsWithValues, $operator, $logicalOperators);
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
        $this->driver->setField($fields);
        return $this->driver->select();
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
        $this->driver->setSet($fieldsWithValues);
        return $this->driver->update();
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
        $this->driver->setSet($fieldsWithValues);
        return $this->driver->insert();
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
        return $this->driver->delete();
    }

    /**
     * Alias for remove method.
     */
    public function delete(): int
    {
        return $this->remove();
    }
}
