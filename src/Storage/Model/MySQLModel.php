<?php

declare(strict_types=1);

namespace Projom\Storage\Model;

use Projom\Storage\Query\MySQLQuery;
use Projom\Storage\SQL\Util\Aggregate;
use Projom\Storage\SQL\Util\Operator;
use Projom\Storage\Util;

/**
 * MySQLModel provides a set of methods to interact with a database table.
 * 
 * How to use:
 * * Extend this class to create a query-able "model/repository" for that table.
 * * The extended class name should be the same as the table name.
 *
 * Mandatory abstract methods to implement: 
 * * primaryField(): string 'FieldID'
 *
 * Optional methods to implement for additional processing:
 *  * formatFields(): array [ 'Field' => 'string', 'AnotherField' => 'int', ... ]
 *  * redactFields(): array [ 'Field', 'AnotherField' ]
 * 
 * The value of all redacted fields will be replaced with the string "\_\_REDACTED\_\_".
 */
trait MySQLModel
{
	private $table = null;
	private $primaryField = null;
	private $formatFields = [];
	private $redactedFields = [];

	abstract public function primaryField(): string;

	public function formatFields(): array
	{
		return [];
	}

	public function redactFields(): array
	{
		return [];
	}

	private function invoke()
	{
		$calledClass = get_class($this);
		$class = basename(str_replace('\\', DIRECTORY_SEPARATOR, $calledClass));
		$this->table = $class;
		$this->primaryField = $this->primaryField();
		$this->formatFields = $this->formatFields();
		$this->redactedFields = $this->redactFields();

		if (!$this->table)
			throw new \Exception('Table name not set', 400);
		if (!$this->primaryField)
			throw new \Exception('Primary field not set', 400);
	}

	private function processRecords(array $records): array
	{
		$records = Util::rekey($records, $this->primaryField);

		$processedRecords = [];
		foreach ($records as $key => $record) {
			$record = $this->formatRecord($record);
			$record = $this->redactRecord($record);
			$processedRecords[$key] = $record;
		}

		return $processedRecords;
	}

	private function formatRecord(array $record): array
	{
		if (!$this->formatFields)
			return $record;

		foreach ($this->formatFields as $field => $type) {
			if (!array_key_exists($field, $record))
				continue;
			$value = $record[$field];
			$record[$field] = Util::format($value, $type);
		}

		return $record;
	}

	private function redactRecord(array $record): array
	{
		if (!$this->redactedFields)
			return $record;

		foreach ($this->redactedFields as $field) {
			if (!array_key_exists($field, $record))
				throw new \Exception("Field: {$field}, could not be redacted. Not found in record", 400);
			$record[$field] = '__REDACTED__';
		}

		return $record;
	}

	/**
	 * Create a record.
	 * 
	 * * Example use: $user->create(['Name' => 'John'])
	 */
	public function create(array $record): int|string
	{
		$this->invoke();
		$primaryID = MySQLQuery::query($this->table)->insert($record);
		return $primaryID;
	}

	/**
	 * Find a record by its primary id.
	 * 
	 * * Example use: $user->find($userID = 3)
	 */
	public function find(string|int $primaryID): null|array|object
	{
		$this->invoke();

		$records = MySQLQuery::query($this->table)->fetch($this->primaryField, $primaryID);
		if (!$records)
			return null;

		$records = $this->processRecords($records);

		return array_pop($records);
	}

	/**
	 * Update a record by its primary id.
	 * 
	 * * Example use: $user->update($userID = 3, ['Name' => 'A new name'])
	 */
	public function update(string|int $primaryID, array $data): void
	{
		$this->invoke();
		MySQLQuery::query($this->table)->filterOn($this->primaryField, $primaryID)->update($data);
	}

	/**
	 * Delete a record by its primary id.
	 * 
	 * * Example use: $user->delete($userID = 3)
	 */
	public function delete(string|int $primaryID): void
	{
		$this->invoke();
		MySQLQuery::query($this->table)->filterOn($this->primaryField, $primaryID)->delete();
	}

	/**
	 * Clone a record.
	 * 
	 * @param array $newRecord used to write new values to fields from the cloned record.
	 * 
	 * * Example use: $user->clone($userID = 3)
	 * * Example use: $user->clone($userID = 3, ['Name' => 'New Name'])
	 */
	public function clone(string|int $primaryID, array $newRecord = []): array|object
	{
		$this->invoke();

		$records = MySQLQuery::query($this->table)->fetch($this->primaryField, $primaryID);
		if (!$records)
			return throw new \Exception('Record to clone not found', 400);

		$record = array_pop($records);
		unset($record[$this->primaryField]);

		// Merge new record with existing record. 
		$record = $newRecord + $record;
		$clonePrimaryID = MySQLQuery::query($this->table)->insert($record);

		$clonedRecords = MySQLQuery::query($this->table)->fetch($this->primaryField, $clonePrimaryID);
		$clonedRecords = $this->processRecords($clonedRecords);
		return array_pop($clonedRecords);
	}

	/**
	 * Get all records.
	 * 
	 * * Example use: $user->all()
	 * * Example use: $user->all($filters = ['Active' => 0])
	 */
	public function all(array $filters = []): null|array
	{
		$this->invoke();

		$query = MySQLQuery::query($this->table);
		if ($filters)
			$query->filterOnFields($filters);

		$records = $query->select();
		if (!$records)
			return null;

		$records = $this->processRecords($records);

		return $records;
	}

	/**
	 * Search for records filtering on field like %value%.
	 * 
	 * * Example use: $user->search('Name', 'John')
	 */
	public function search(string $field, string $value): null|array
	{
		$this->invoke();

		$records = MySQLQuery::query($this->table)->filterOn($field, "%$value%", Operator::LIKE)->select();
		if (!$records)
			return null;

		$records = $this->processRecords($records);

		return $records;
	}

	/**
	 * Get a record by filtering on field with value.
	 * 
	 * * Example use: $user->get('Email', 'John.doe@example.com')
	 */
	public function get(string $field, mixed $value): null|array|object
	{
		$this->invoke();

		$records = MySQLQuery::query($this->table)->fetch($field, $value);
		if (!$records)
			return null;

		$records = $this->processRecords($records);

		if (count($records) === 1)
			return array_pop($records);

		return $records;
	}

	/**
	 * Count records.
	 * 
	 * * Example use: $user->count()
	 * * Example use: $user->count(filters: ['Active' => 0])
	 * * Example use: $user->count('UserID', groupFields: ['Active'])
	 */
	public function count(string $countField = '*',  array $filters = [], array $groupByFields = []): null|array
	{
		$this->invoke();

		$query = MySQLQuery::query($this->table);

		if ($filters)
			$query->filterOnFields($filters);

		$aggregate = Aggregate::COUNT->buildSQL($countField, 'count');
		$fields = [$aggregate];

		if ($groupByFields) {
			$query->groupOn(...$groupByFields);
			$fields = Util::merge($fields, $groupByFields);
		}

		$records = $query->select(...$fields);
		if (!$records)
			return null;

		return $records;
	}

	/**
	 * Summarize records.
	 * 
	 * * Example use: Invoice::sum('Amount')
	 * * Example use: Invoice::sum('Amount', ['Paid' => 0, 'Due' => '2024-07-25'])
	 * * Example use: Invoice::sum('Amount', groupFields: ['Paid'])
	 */
	public function sum(string $sumField, array $filters = [], array $groupByFields = []): null|array
	{
		$this->invoke();

		$query = MySQLQuery::query($this->table);

		if ($filters)
			$query->filterOnFields($filters);

		$aggregate = Aggregate::SUM->buildSQL($sumField, 'sum');
		$fields = [$aggregate];

		if ($groupByFields) {
			$query->groupOn(...$groupByFields);
			$fields = Util::merge($fields, $groupByFields);
		}

		$records = $query->select(...$fields);
		if (!$records)
			return null;

		return $records;
	}

	/**
	 * Average records.
	 * 
	 * * Example use: Invoice::avg('Amount')
	 * * Example use: Invoice::avg('Amount', ['Paid' => 0, 'Due' => '2024-07-25'])
	 * * Example use: Invoice::avg('Amount', groupFields: ['Paid'])
	 */
	public function avg(string $averageField, array $filters = [], array $groupByFields = []): null|array
	{
		$this->invoke();

		$query = MySQLQuery::query($this->table);

		if ($filters)
			$query->filterOnFields($filters);

		$aggregate = Aggregate::AVG->buildSQL($averageField, 'avg');
		$fields = [$aggregate];

		if ($groupByFields) {
			$query->groupOn(...$groupByFields);
			$fields = Util::merge($fields, $groupByFields);
		}

		$records = $query->select(...$fields);
		if (!$records)
			return null;

		return $records;
	}

	/**
	 * Minimum of records.
	 * 
	 * * Example use: Invoice::min('Amount')
	 * * Example use: Invoice::min('Amount', ['Paid' => 0, 'Due' => '2024-07-25'])
	 * * Example use: Invoice::min('Amount', groupFields: ['Paid'])
	 */
	public function min(string $minField, array $filters = [], array $groupByFields = []): null|array
	{
		$this->invoke();

		$query = MySQLQuery::query($this->table);

		if ($filters)
			$query->filterOnFields($filters);

		$aggregate = Aggregate::MIN->buildSQL($minField, 'min');
		$fields = [$aggregate];

		if ($groupByFields) {
			$query->groupOn(...$groupByFields);
			$fields = Util::merge($fields, $groupByFields);
		}

		$records = $query->select(...$fields);
		if (!$records)
			return null;

		return $records;
	}

	/**
	 * Maximum of records.
	 * 
	 * * Example use: Invoice::max('Amount')
	 * * Example use: Invoice::max('Amount', ['Paid' => 0, 'Due' => '2024-07-25'])
	 * * Example use: Invoice::max('Amount', groupFields: ['Paid'])
	 */
	public function max(string $maxField, array $filters = [], array $groupByFields = []): null|array
	{
		$this->invoke();

		$query = MySQLQuery::query($this->table);

		if ($filters)
			$query->filterOnFields($filters);

		$aggregate = Aggregate::MAX->buildSQL($maxField, 'max');
		$fields = [$aggregate];

		if ($groupByFields) {
			$query->groupOn(...$groupByFields);
			$fields = Util::merge($fields, $groupByFields);
		}

		$records = $query->select(...$fields);
		if (!$records)
			return null;

		return $records;
	}

	/**
	 * Paginate records.
	 * 
	 * * Example use: $user->paginate(1, 10)
	 * * Example use: $user->paginate(1, 10, ['Name' => 'John'])
	 */
	public function paginate(int $page, int $pageSize, array $filters = []): null|array
	{
		$this->invoke();

		$query = MySQLQuery::query($this->table);

		if ($filters)
			$query->filterOnFields($filters);

		$offset = ($page - 1) * $pageSize;
		$query->offset($offset)->limit($pageSize);

		$records = $query->select();
		if (!$records)
			return null;

		$records = $this->processRecords($records);

		return $records;
	}
}
