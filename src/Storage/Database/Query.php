<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\Engine\DriverInterface;
use Projom\Storage\Database\Query\LogicalOperator;
use Projom\Storage\Database\Query\Operator;
use Projom\Storage\Database\Query\QueryObject;

class Query
{
    private DriverInterface|null $driver = null;
    private array $collections = [];
    private array $filters = [];
    private array $sorts = [];
    private int|string $limit = '';
    private array $groups = [];

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
    public function fetch(string $field, mixed $value, Operator $operator = Operator::EQ): array
    {
        $this->filterOn([$field => $value], $operator);
        return $this->select('*');
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
        $queryObject = new QueryObject(
            collections: $this->collections,
            fields: $fields,
            filters: $this->filters,
            sorts: $this->sorts,
            limit: $this->limit,
            groups: $this->groups
        );
        return $this->driver->select($queryObject);
    }

    /**
     * Alias for select method.
     */
    public function get(string ...$fields): array
    {
        return $this->select(...$fields);
    }

    /**
     * Executes a query updating record(s) and returns the number of affected rows.
     * 
     * * Example use: $database->query('CollectionName')->update(['Active' => 1])
     * * Example use: $database->query('CollectionName')->filterOn( ... )->update(['Username' => 'Jane', 'Password' => 'password'])
     */
    public function update(array $fieldsWithValues): int
    {
        $queryObject = new QueryObject(
            collections: $this->collections,
            fieldsWithValues: $fieldsWithValues,
            filters: $this->filters
        );
        return $this->driver->update($queryObject);
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
        $queryObject = new QueryObject(
            collections: $this->collections,
            fieldsWithValues: $fieldsWithValues
        );
        return $this->driver->insert($queryObject);
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
        $queryObject = new QueryObject(
            collections: $this->collections,
            filters: $this->filters
        );
        return $this->driver->delete($queryObject);
    }

    /**
     * Alias for delete method.
     */
    public function remove(): int
    {
        return $this->delete();
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
        Operator $operator = Operator::EQ,
        LogicalOperator $logicalOperator = LogicalOperator::AND
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
     * Grouping the result of the query.
     * 
     * * Example use: $database->query('CollectionName')->groupOn('Name')->get('*')
     * * Example use: $database->query('CollectionName')->groupOn('Name', 'Age')->get('*')
     * * Example use: $database->query('CollectionName')->groupOn('Name, Age')->get('*');
     */
    public function groupOn(string ...$fields): Query
    {
        $this->groups[] = $fields;
        return $this;
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
}
