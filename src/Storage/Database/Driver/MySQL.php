<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Driver\MySQL\Column;
use Projom\Storage\Database\Driver\MySQL\Filter;
use Projom\Storage\Database\Driver\MySQL\Set;
use Projom\Storage\Database\Driver\MySQL\Statement;
use Projom\Storage\Database\Driver\MySQL\Table;
use Projom\Storage\Database\Drivers;
use Projom\Storage\Database\LogicalOperators;
use Projom\Storage\Database\Operators;
use Projom\Storage\Database\Query;
use Projom\Storage\Database\Query\Collection as QCollection;
use Projom\Storage\Database\SourceInterface;
use Projom\Storage\Database\Source\PDOSource;

class MySQL implements DriverInterface
{
	private PDOSource $source;
	protected Drivers $driver = Drivers::MySQL;
	private Filter $filter;
	private Column $column;
	private Set $set;

	public function __construct(PDOSource $source)
	{
		$this->source = $source;
		$this->filter = Filter::create([]);
	}

	public static function create(SourceInterface $source): MySQL
	{
		return new MySQL($source);
	}

	public function type(): Drivers
	{
		return $this->driver;
	}

	public function setField(array $fields): void
	{
		$this->column = Column::create($fields);
	}

	public function setFilter(
		array $fieldsWithValues,
		Operators $operator,
		LogicalOperators $logicalOperators
	): void {
		$filter = Filter::create($fieldsWithValues, $operator, $logicalOperators);
		if ($this->filter === null)
			$this->filter = $filter;
		else
			$this->filter->merge($filter);
	}

	public function setSet(array $fieldsWithValues): void
	{
		$this->set = Set::create($fieldsWithValues);
	}

	public function select(QCollection $collection): array
	{
		$table = Table::create($collection->get());
		$this->column->parse();
		$this->filter->parse();

		[$query, $params] = Statement::select($table, $this->column, $this->filter);

		return $this->source->execute($query, $params);
	}

	public function update(QCollection $collection): int
	{
		$table = Table::create($collection->get());
		$this->set->parse();
		$this->filter->parse();

		[$query, $params] = Statement::update($table, $this->set, $this->filter);

		$this->source->execute($query, $params);

		return $this->source->rowsAffected();
	}

	public function insert(QCollection $collection): int
	{
		$table = Table::create($collection->get());
		$this->set->parse();

		[$query, $params] = Statement::insert($table, $this->set);

		$this->source->execute($query, $params);

		return $this->source->lastInsertedID();
	}

	public function delete(QCollection $collection): int
	{
		$table = Table::create($collection->get());
		$this->filter->parse();

		[$query, $params] = Statement::delete($table, $this->filter);

		$this->source->execute($query, $params);

		return $this->source->rowsAffected();
	}

	public function Query(string $table): Query
	{
		return Query::create($this, $table);
	}

	public function execute(string $sql, ?array $params): array
	{
		return $this->source->execute($sql, $params);
	}
}
