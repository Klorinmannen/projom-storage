<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\LogicalOperators;
use Projom\Storage\Database\Operators;
use Projom\Storage\Database\Query\Delete;
use Projom\Storage\Database\Query\Insert;
use Projom\Storage\Database\Query\Select;
use Projom\Storage\Database\Query\Update;

class Query
{
    private DriverInterface|null $driver = null;

    private array $collections = [];
    private array $filters = [];
    private array $sorts = [];
    private int|string $limit = '';

    public function __construct(DriverInterface $driver, array $collections)
    {
        $this->collections = $collections;
        $this->driver = $driver;
    }

    public static function create(DriverInterface $driver, array $collections): Query
    {
        return new Query($driver, $collections);
    }

    /**
     * Simple query mechanism to find record(s) by a field and its value.
     * 
     * * Example use: $database->query('CollectionName')->fetch('Name', 'John')
     * * Example use: $database->query('CollectionName')->fetch('Age', [25, 55], Operators::IN)
     */
    public function fetch(string $field, mixed $value, Operators $operator = Operators::EQ): array
    {
        $this->filterOn([$field => $value], $operator);
        return $this->get($field);
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
        LogicalOperators $logicalOperator = LogicalOperators::AND
    ): Query {

        foreach ($fieldsWithValues as $field => $value) {
            $this->filters[] = [
                $field,
                $operator,
                $value,
                $logicalOperator
            ];
        }

        return $this;
    }

    /**
     * Executes a query finding record(s) and returns the result.
     * 
     * * Example use: $database->query('CollectionName')->select('*')
     * * Example use: $database->query('CollectionName')->select('Name', 'Age')
     * * Example use: $database->query('CollectionName')->select('Name, Age')
     * * Example use: $database->query('CollectionName')->select('Name as Username')
     * * Example use: $database->query('CollectionName')->select([ 'Name', 'Age', 'Username' ])
     */
    public function select(string ...$fields): array
    {
        $select = new Select($this->collections, $fields, $this->filters, $this->sorts, $this->limit);
        return $this->driver->select($select);
    }

    /**
     * Alias for select method.
     */
    public function get(string ...$fields): array
    {
        return $this->select(...$fields);
    }

    /**
     * Sorts the result of the query.
     * 
     * * Example use: $database->query('CollectionName')->sortOn(['Name' => Sorts::DESC])->get('*')
     */
    public function sortOn(array $sortFields): Query
    {
        foreach ($sortFields as $field => $sort)
            $this->sorts[] = [$field, $sort];
        return $this;
    }

    /**
     * Limits the result of the query.
     * 
     * * Example use: $database->query('CollectionName')->limit(10)->get('*')
     */
    public function limit(int|string $limit): Query
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Executes a query updating record(s) and returns the number of affected rows.
     * 
     * * Example use: $database->query('CollectionName')->update(['Active' => 1])
     * * Example use: $database->query('CollectionName')->filterOn( ... )->update(['Username' => 'Jane', 'Password' => 'password'])
     */
    public function update(array $fieldsWithValues): int
    {
        $update = new Update($this->collections, $fieldsWithValues, $this->filters);
        return $this->driver->update($update);
    }
    /**
     * Alias for update method.
     */
    public function modify(array $fieldsWithValues): int
    {
        return $this->update($fieldsWithValues);
    }

    /**
     * Executes a query inserting a record and returns the latest inserted primary id.
     * 
     * * Example use: $database->query('CollectionName')->insert(['Username' => 'John', 'Password' => '1234'])
     */
    public function insert(array $fieldsWithValues): int
    {
        $insert = new Insert($this->collections, $fieldsWithValues);
        return $this->driver->insert($insert);
    }

    /**
     * Alias for insert method.
     */
    public function add(array $fieldsWithValues): int
    {
        return $this->insert($fieldsWithValues);
    }

    /**
     * Executes a query to delete record(s) and returns the number of affected rows.
     * 
     * * Example use: $database->query('CollectionName')->filterOn( ... )->delete()
     */
    public function delete(): int
    {
        $delete = new Delete($this->collections, $this->filters);
        return $this->driver->delete($delete);
    }

    /**
     * Alias for delete method.
     */
    public function remove(): int
    {
        return $this->delete();
    }
}
