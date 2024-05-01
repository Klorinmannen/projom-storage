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
use Projom\Storage\Database\SourceInterface;
use Projom\Storage\Database\Source\PDOSource;

class MySQL implements DriverInterface
{
	private PDOSource $source;
	protected Drivers $driver = Drivers::MySQL;
	private Table $table;
	private Column $column;
	private Filter $filter;
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

	public function setTable(string $table): void
	{
		$this->table = Table::create($table);
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

	public function select(): array
	{
		$this->filter->parse();

		[$query, $params] = Statement::select($this->table, $this->column, $this->filter);

		return $this->source->execute($query, $params);
	}

	public function update(): int
	{
		$this->filter->parse();

		[$query, $params] = Statement::update($this->table, $this->set, $this->filter);

		$this->source->execute($query, $params);

		return $this->source->rowsAffected();
	}

	public function insert(): int
	{
		[$query, $params] = Statement::insert($this->table, $this->set);

		$this->source->execute($query, $params);

		return $this->source->lastInsertedID();
	}

	public function delete(): int
	{
		$this->filter->parse();

		[$query, $params] = Statement::delete($this->table, $this->filter);

		$this->source->execute($query, $params);

		return $this->source->rowsAffected();
	}

	public function Query(string $table): Query
	{
		$this->setTable($table);
		return Query::create($this);
	}

	public function execute(string $sql, ?array $params): array
	{
		return $this->source->execute($sql, $params);
	}
}
