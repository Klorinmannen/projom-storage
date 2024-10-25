<?php

declare(strict_types=1);

namespace Projom\Storage\SQL;

use Projom\Storage\Action;
use Projom\Storage\Engine\DriverBase;
use Projom\Storage\Format;
use Projom\Storage\SQL\QueryObject;
use Projom\Storage\SQL\Util\Join;
use Projom\Storage\SQL\Util\LogicalOperator;
use Projom\Storage\SQL\Util\Operator;
use Projom\Storage\SQL\Util\Filter;

class QueryBuilder
{
    private null|DriverBase $driver = null;
    private array $collections = [];
    private array $fields = [];
    private array $filters = [];
    private array $sorts = [];
    private array $joins = [];
    private array $groups = [];
    private null|int $limit = null;
    private null|int $offset = null;
    private array $formatting = [];

    private const DEFAULT_SELECT = '*';

    public function __construct(null|DriverBase $driver, array $collections)
    {
        $this->collections = $collections;
        $this->driver = $driver;
        $this->fields = [static::DEFAULT_SELECT];
        $this->formatting = [Format::ARRAY, null];
    }

    public static function create(null|DriverBase $driver = null, array $collections = []): QueryBuilder
    {
        return new QueryBuilder($driver, $collections);
    }

    /**
     * Format the query result.
     * 
     * * Example use: $database->query('CollectionName')->formatAs(Format::STD_CLASS)
     * * Example use: $database->query('CollectionName')->formatAs(Format::CUSTOM_OBJECT, ClassName::class)
     */
    public function formatAs(Format $format, mixed $args = null): QueryBuilder
    {
        $this->formatting = [$format, $args];
        return $this;
    }

    /**
     * Simple query mechanism to find record(s) by a field and its value.
     * 
     * * Example use: $database->query('CollectionName')->fetch('Name', 'John')
     * * Example use: $database->query('CollectionName')->fetch('Age', [25, 55], Operator::IN)
     */
    public function fetch(string $field, mixed $value, Operator $operator = Operator::EQ): null|array
    {
        $this->filterOn($field, $value, $operator);
        return $this->select(static::DEFAULT_SELECT);
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
    public function select(string ...$fields): null|array
    {
        if ($fields)
            $this->fields = $fields;

        $queryObject = new QueryObject(
            collections: $this->collections,
            fields: $this->fields,
            filters: $this->filters,
            sorts: $this->sorts,
            groups: $this->groups,
            limit: $this->limit,
            offset: $this->offset,
            joins: $this->joins,
            formatting: $this->formatting
        );
        return $this->driver->dispatch(Action::SELECT, $queryObject);
    }

    /**
     * Alias for select method.
     */
    public function get(string ...$fields): null|array
    {
        return $this->select(...$fields);
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
        return $this->insertMultiple([$fieldsWithValues]);
    }

    /**
     * Alias for insert method.
     */
    public function add(array $fieldsWithValues): int
    {
        return $this->insertMultiple([$fieldsWithValues]);
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
    ): QueryBuilder {

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
     * @param array $filter ['Name', Operator::EQ, 'John', LogicalOperator::AND]
     * 
     * * Example use: $database->query('CollectionName')->filter(['Name', Operator::EQ, 'John', LogicalOperator::AND])
     */
    public function filter(array $filter, LogicalOperator $groupLogicalOperator = LogicalOperator::AND): QueryBuilder 
    {
        $this->filters[] = [[$filter], $groupLogicalOperator];
        return $this;
    }

    /**
     * Create a filter to be used in the query to be executed.
     * 
     * @param array $filters [['Name', Operator::EQ, 'John', LogicalOperator::AND], [ ... ], [ ... ]]
     * 
     * * Example use: $database->query('CollectionName')->filterList([['Name', Operator::EQ, 'John', LogicalOperator::AND]])
     */
    public function filterList(array $filters, LogicalOperator $groupLogicalOperator = LogicalOperator::AND): QueryBuilder
    {
        $this->filters[] = [$filters, $groupLogicalOperator];
        return $this;
    }

    /**
     * Create a filter to be used in the query to be executed.
     * 
     * @param array $fieldsWithValues ['Name' => 'John', 'Lastname' => 'Doe', 'UserID' => 25, ..., ...]
     *
     * * Example use: $database->query('CollectionName')->filterList(['Name' => 'John', 'Deleted' => 0 ])
     */
    public function filterOnFields(
        array $fieldsWithValues,
        Operator $operator = Operator::EQ,
        LogicalOperator $logicalOperator = LogicalOperator::AND
    ): QueryBuilder {

        $filters = Filter::list($fieldsWithValues, $operator);
        $this->filterList($filters, $logicalOperator);

        return $this;
    }

    /**
     * Create a filter to be used in the query to be executed.
     * 
     * @param string $field 'Name'
     * @param mixed $value 'John'
     * 
     * * Example use: $database->query('CollectionName')->filterOn('Name', 'John')
     */
    public function filterOn(
        string $field,
        mixed $value,
        Operator $operator = Operator::EQ,
        LogicalOperator $logicalOperator = LogicalOperator::AND
    ): QueryBuilder {

        $filter = Filter::build($field, $value, $operator);
        $this->filter($filter, $logicalOperator);

        return $this;
    }

    /**
     * Group the query result.
     * 
     * * Example use: $database->query('CollectionName')->groupOn('Name')
     * * Example use: $database->query('CollectionName')->groupOn('Name', 'Age')
     * * Example use: $database->query('CollectionName')->groupOn('Name, Age')
     */
    public function groupOn(string ...$fields): QueryBuilder
    {
        if ($fields)
            $this->groups[] = $fields;
        return $this;
    }

    /**
     * Order the query result.
     * 
     * * Example use: $database->query('CollectionName')->sortOn(['Name' => Sorts::DESC])
     * * Example use: $database->query('CollectionName')->sortOn(['Name' => Sorts::ASC, 'Age' => Sorts::DESC])
     */
    public function orderOn(array $sortFields): QueryBuilder
    {
        foreach ($sortFields as $field => $sort)
            $this->sorts[] = [$field, $sort];
        return $this;
    }

    /**
     * Alias for order method.
     */
    public function sortOn(array $sortFields): QueryBuilder
    {
        return $this->orderOn($sortFields);
    }

    /**
     * Limit the query result.
     * 
     * * Example use: $database->query('CollectionName')->limit(10)
     * * Example use: $database->query('CollectionName')->limit('10')
     */
    public function limit(int $limit): QueryBuilder
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Offset the query result.
     * 
     * * Example use: $database->query('CollectionName')->offset(10)
     */
    public function offset(int $offset): QueryBuilder
    {
        $this->limit = $offset;
        return $this;
    }
}
