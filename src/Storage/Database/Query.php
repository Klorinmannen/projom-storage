<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\Engine\DriverInterface;
use Projom\Storage\Database\Query\Action;
use Projom\Storage\Database\Query\AggregateFunction;
use Projom\Storage\Database\Query\Filter;
use Projom\Storage\Database\Query\Join;
use Projom\Storage\Database\Query\LogicalOperator;
use Projom\Storage\Database\Query\Operator;
use Projom\Storage\Database\Query\QueryObject;

class Query
{
    private DriverInterface|null $driver = null;
    private array $collections = [];
    private array $filters = [];
    private array $sorts = [];
    private array $joins = [];
    private array $groups = [];
    private int|string $limit = '';

    private const DEFAULT_SELECT = '*';

    public function __construct(DriverInterface|null $driver, array $collections)
    {
        $this->collections = $collections;
        $this->driver = $driver;
    }

    public static function create(DriverInterface|null $driver = null, array $collections = []): Query
    {
        return new Query($driver, $collections);
    }

    /**
     * Simple query mechanism to find record(s) by a field and its value.
     * 
     * * Example use: $database->query('CollectionName')->fetch('Name', 'John')
     * * Example use: $database->query('CollectionName')->fetch('Age', [25, 55], Operator::IN)
     */
    public function fetch(string $field, mixed $value, Operator $operator = Operator::EQ): array
    {
        $this->filterOn($field, $value, $operator);
        return $this->select('*');
    }

    /**
     * Execute a query finding record(s) and returns the result.
     * 
     * * Example use: $database->query('CollectionName')->select('*')
     * * Example use: $database->query('CollectionName')->select('Name', 'Age')
     * * Example use: $database->query('CollectionName')->select('Name, Age')
     * * Example use: $database->query('CollectionName')->select('Name as Username')
     * * Example use: $database->query('CollectionName')->select([ 'Name', 'Age', 'Username' ])
     */
    public function select(string ...$fields): array
    {
        $fields = $fields ?: [static::DEFAULT_SELECT];

        $queryObject = new QueryObject(
            collections: $this->collections,
            fields: $fields,
            filters: $this->filters,
            sorts: $this->sorts,
            groups: $this->groups,
            limit: $this->limit,
            joins: $this->joins
        );
        return $this->driver->dispatch(Action::SELECT, $queryObject);
    }

    /**
     * Alias for select method.
     */
    public function get(string ...$fields): array
    {
        return $this->select(...$fields);
    }

    public function count(string $field = '*'): null|int
    {
        $queryObject = new QueryObject(
            collections: $this->collections,
            fields: [$field],
            filters: $this->filters,
            sorts: $this->sorts,
            groups: $this->groups,
            limit: $this->limit,
            joins: $this->joins
        );
        return $this->driver->dispatch(Action::COUNT, $queryObject);
    }

    public function sum(string $field): null|int|float
    {
        $queryObject = new QueryObject(
            collections: $this->collections,
            fields: [$field],
            filters: $this->filters,
            sorts: $this->sorts,
            groups: $this->groups,
            limit: $this->limit,
            joins: $this->joins
        );
        return $this->driver->dispatch(Action::SUM, $queryObject);
    }

    public function avg(string $field): null|float
    {
        $queryObject = new QueryObject(
            collections: $this->collections,
            fields: [$field],
            filters: $this->filters,
            sorts: $this->sorts,
            groups: $this->groups,
            limit: $this->limit,
            joins: $this->joins
        );
        return $this->driver->dispatch(Action::AVG, $queryObject);
    }

    public function max(string $field): null|string
    {
        $queryObject = new QueryObject(
            collections: $this->collections,
            fields: [$field],
            filters: $this->filters,
            sorts: $this->sorts,
            groups: $this->groups,
            limit: $this->limit,
            joins: $this->joins
        );
        return $this->driver->dispatch(Action::MAX, $queryObject);
    }

    public function min(string $field): null|string
    {
        $queryObject = new QueryObject(
            collections: $this->collections,
            fields: [$field],
            filters: $this->filters,
            sorts: $this->sorts,
            groups: $this->groups,
            limit: $this->limit,
            joins: $this->joins
        );
        return $this->driver->dispatch(Action::MIN, $queryObject);
    }

    /**
     * Execute a query updating record(s) and return the number of affected rows.
     * 
     * * Example use: $database->query('CollectionName')->update(['Active' => 1])
     * * Example use: $database->query('CollectionName')->filterOn( ... )->update(['Username' => 'Jane', 'Password' => 'password'])
     */
    public function update(array $fieldsWithValues): int
    {
        $queryObject = new QueryObject(
            collections: $this->collections,
            fieldsWithValues: [$fieldsWithValues],
            filters: $this->filters,
            joins: $this->joins
        );
        return $this->driver->dispatch(Action::UPDATE, $queryObject);
    }

    /**
     * Alias for update method.
     */
    public function modify(array $fieldsWithValues): int
    {
        return $this->update($fieldsWithValues);
    }

    /**
     * Execute a query inserting multiple records in a single statement.
     * Returns the primary id of the first inserted record.
     * 
     * * Example use: $database->query('CollectionName')->insert([['Username' => 'John', 'Password' => '1234']])
     */
    public function insertMultiple(array $fieldsWithValues): int
    {
        $queryObject = new QueryObject(
            collections: $this->collections,
            fieldsWithValues: $fieldsWithValues
        );
        return $this->driver->dispatch(Action::INSERT, $queryObject);
    }

    /**
     * Alias for insert multiple method.
     */
    public function addMultiple(array $fieldsWithValues): int
    {
        return $this->insertMultiple($fieldsWithValues);
    }

    /**
     * Execute a query inserting a record and returns the corresponding primary id.
     * 
     * * Example use: $database->query('CollectionName')->insert(['Username' => 'John', 'Password' => '1234'])
     */
    public function insert(array $fieldsWithValues): int
    {
        $queryObject = new QueryObject(
            collections: $this->collections,
            fieldsWithValues: [$fieldsWithValues]
        );
        return $this->driver->dispatch(Action::INSERT, $queryObject);
    }

    /**
     * Alias for insert method.
     */
    public function add(array $fieldsWithValues): int
    {
        return $this->insert($fieldsWithValues);
    }

    /**
     * Execute a query to delete record(s) and returns the number of affected rows.
     * 
     * * Example use: $database->query('CollectionName')->filterOn( ... )->delete()
     */
    public function delete(): int
    {
        $queryObject = new QueryObject(
            collections: $this->collections,
            filters: $this->filters,
            joins: $this->joins
        );
        return $this->driver->dispatch(Action::DELETE, $queryObject);
    }

    /**
     * Alias for delete method.
     */
    public function destroy(): int
    {
        return $this->delete();
    }

    /**
     * Join collections to the query.
     * 
     * * Example use: $database->query('CollectionName')->join('Collection1.Field', Join::INNER, 'Collection2.Field')
     * * Example use: $database->query('CollectionName')->join('Collection1.Field = Collection2.Field', Join::LEFT)
     */
    public function joinOn(
        string $currentCollectionWithField,
        Join $join,
        string|null $onCollectionWithField = null
    ): Query {

        $this->joins[] = [
            $currentCollectionWithField,
            $join,
            $onCollectionWithField
        ];

        return $this;
    }

    /**
     * Create a filter to be used in the query to be executed.
     * 
     * @param array $filterOnGroup [['Name', Operator::EQ, 'John', LogicalOperator::AND], [ ... ], [ ... ]]
     */
    public function filterOnGroup(
        array $filterGroup,
        LogicalOperator $groupLogicalOperator = LogicalOperator::AND
    ): Query {

        $this->filters[] = [$filterGroup, $groupLogicalOperator];

        return $this;
    }

    /**
     * Create a filter to be used in the query to be executed.
     * 
     * @param array $fieldsWithValues ['Name' => 'John', 'Lastname' => 'Doe', 'UserID' => 25, ..., ...]
     *
     * * Example use: $database->query('CollectionName')->filterList(['Name' => 'John', 'Deleted' => 0 ])
     */
    public function filterOnList(
        array $fieldsWithValues,
        Operator $operator = Operator::EQ,
        LogicalOperator $logicalOperator = LogicalOperator::AND
    ): Query {

        $filter = Filter::buildGroup($fieldsWithValues, $operator);
        $this->filterOnGroup($filter, $logicalOperator);

        return $this;
    }

    /**
     * Create a filter to be used in the query to be executed.
     * 
     * * Example use: $database->query('CollectionName')->filterOn('Name', 'John')
     */
    public function filterOn(
        string $field,
        mixed $value,
        Operator $operator = Operator::EQ,
        LogicalOperator $logicalOperator = LogicalOperator::AND
    ): Query {

        $filter = Filter::buildGroup([$field => $value], $operator);
        $this->filterOnGroup($filter, $logicalOperator);

        return $this;
    }

    /**
     * Group the query result.
     * 
     * * Example use: $database->query('CollectionName')->groupOn('Name')
     * * Example use: $database->query('CollectionName')->groupOn('Name', 'Age')
     * * Example use: $database->query('CollectionName')->groupOn('Name, Age')
     */
    public function groupOn(string ...$fields): Query
    {
        $this->groups[] = $fields;
        return $this;
    }

    /**
     * Order the query result.
     * 
     * * Example use: $database->query('CollectionName')->sortOn(['Name' => Sorts::DESC])
     * * Example use: $database->query('CollectionName')->sortOn(['Name' => Sorts::ASC, 'Age' => Sorts::DESC])
     */
    public function orderOn(array $sortFields): Query
    {
        foreach ($sortFields as $field => $sort)
            $this->sorts[] = [$field, $sort];
        return $this;
    }

    /**
     * Alias for order method.
     */
    public function sortOn(array $sortFields): Query
    {
        return $this->orderOn($sortFields);
        return $this;
    }

    /**
     * Limit the query result.
     * 
     * * Example use: $database->query('CollectionName')->limit(10)
     * * Example use: $database->query('CollectionName')->limit('10')
     */
    public function limit(int|string $limit): Query
    {
        $this->limit = $limit;
        return $this;
    }
}
