<?php

declare(strict_types=1);

namespace Projom\Storage\MySQL;

use Exception;

use Projom\Storage\MySQL\Query;
use Projom\Storage\MySQL\Util;
use Projom\Storage\SQL\Util\Aggregate;
use Projom\Storage\SQL\Util\Operator;

/**
 * Repository is a trait that provides a set of methods to interact with a database table.
 * 
 * How to use:
 * * Use this trait to create a query-able "repository" of the class using the trait.
 * * The name of the class using the trait should be the same as the database table name.
 *
 * Optional methods to implement for additional processing:
 * * formatFields(): array [ 'Field' => 'string', 'AnotherField' => 'int', ... ]
 * * redactFields(): array [ 'Field', 'AnotherField' ]
 * 
 * The value of all redacted fields will be replaced with the string "__REDACTED__".
 */
trait Repository
{
	private const REDACTED = '__REDACTED__';

	private readonly Query $query;
	private readonly string $table;
	private readonly string $primaryField;

	/**
	 * Invoke / construct the repository.
	 */
	public function invoke(Query $query): void
	{
		$this->query = $query;
		$this->primaryField = $this->primaryField();
		$this->table = $this->table();
	}

	/**
	 * Returns the primary field.
	 * 
	 * Override this method to set a custom primary field.
	 * 
	 * Default: Primary field will be derived from the class name or namespace.
	 */
	public function primaryField(): string
	{
		return Util::dynamicPrimaryField(static::class, $this->useNamespaceAsTableName());
	}

	/**
	 * Returns the table name.
	 * 
	 * Override this method to set a custom table name.
	 * 
	 * Default: The table name will be derived from the class name or namespace.
	 */
	public function table(): string
	{
		return Util::dynamicTableName(static::class, $this->useNamespaceAsTableName());
	}

	/**
	 * Returns whether to use the namespace as the table name.
	 * 
	 * If true, the table name will be derived from the namespace of the class.
	 * Example: `App\Recipe\Ingredient\Repository` will become `RecipeIngredient`.
	 *
	 * If false, the table name will be derived from the class name.
	 * Example: `App\Recipe\IngredientRepository` will become `Ingredient`.
	 *  
	 * Default is false.
	 */
	public function useNamespaceAsTableName(): bool
	{
		return false;
	}

	/**
	 * Returns which fields to format.
	 * 
	 * * Example: ['Name' => 'string', 'Price' => 'int']
	 */
	public function formatFields(): array
	{
		return [];
	}

	/**
	 * Returns which fields to redact.
	 * 
	 * * Example: ['Email', 'Password']
	 */
	public function redactFields(): array
	{
		return [];
	}

	/**
	 * Returns which fields to select.
	 * 
	 * * Example: ['Name', 'Email']
	 */
	public function selectFields(): array
	{
		return [];
	}

	/**
	 * Returns which fields to translate.
	 * 
	 * * Example: ['Name' => 'translated_name', 'Description' => 'translated_description']
	 */
	public function translateFields(): array
	{
		return [];
	}

	private function processRecords(array $records): array
	{
		$records = Util::rekey($records, $this->primaryField);

		$processedRecords = [];
		foreach ($records as $key => $record) {
			$record = $this->selectRecordFields($record);
			$record = $this->formatRecord($record);
			$record = $this->redactRecord($record);
			$record = $this->translateRecordFields($record);
			$processedRecords[$key] = $record;
		}

		return $processedRecords;
	}

	private function formatRecord(array $record): array
	{
		if (!$formatFields = $this->formatFields())
			return $record;

		foreach ($formatFields as $field => $type) {
			if (!array_key_exists($field, $record))
				continue;
			$value = $record[$field];
			$record[$field] = Util::format($value, $type);
		}

		return $record;
	}

	private function redactRecord(array $record): array
	{
		if (!$redactedFields = $this->redactFields())
			return $record;

		foreach ($redactedFields as $field)
			if (array_key_exists($field, $record))
				$record[$field] = static::REDACTED;

		return $record;
	}

	private function selectRecordFields(array $record): array
	{
		if (!$selectFields = $this->selectFields())
			return $record;

		$modifiedRecord = [];
		foreach ($selectFields as $field)
			if (array_key_exists($field, $record))
				$modifiedRecord[$field] = $record[$field];

		return $modifiedRecord;
	}

	private function translateRecordFields(array $record): array
	{
		if (!$translateFields = $this->translateFields())
			return $record;

		$translatedRecord = [];
		foreach ($translateFields as $field => $translatedField)
			if (array_key_exists($field, $record))
				$translatedRecord[$translatedField] = $record[$field];

		return $translatedRecord;
	}

	/**
	 * Create a record.
	 * 
	 * * Example use: $user->create(['Name' => 'John'])
	 */
	public function create(array $record): int|string
	{
		$primaryID = $this->query->build($this->table)->insert($record);
		return $primaryID;
	}

	/**
	 * Find a record by its primary id.
	 * 
	 * * Example use: $user->find($userID = 3)
	 */
	public function find(string|int $primaryID): null|array|object
	{
		$records = $this->query->build($this->table)->fetch($this->primaryField, $primaryID);
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
		$this->query->build($this->table)
			->filterOn($this->primaryField, $primaryID)
			->update($data);
	}

	/**
	 * Delete a record by its primary id.
	 * 
	 * * Example use: $user->delete($userID = 3)
	 */
	public function delete(string|int $primaryID): void
	{
		$this->query->build($this->table)
			->filterOn($this->primaryField, $primaryID)
			->delete();
	}

	/**
	 * Delete records filtered on the given field and value.
	 * 
	 * * Example use: $user->deleteWith('Email', 'john@example.com')
	 */
	public function deleteWith(string $field, mixed $value): void
	{
		$this->query->build($this->table)
			->filterOn($field, $value)
			->delete();
	}

	/**
	 * Delete all records.
	 * 
	 * if no filters are given, all records will be deleted.
	 * 
	 * * Example use: $log->deleteAll(filters: ['Type' => 'error'])
	 */
	public function deleteAll(array $filters = []): void
	{
		$query = $this->query->build($this->table);

		if ($filters)
			$query->filterOnFields($filters);

		$query->delete();
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
		$records = $this->query->build($this->table)->fetch($this->primaryField, $primaryID);
		if (!$records)
			return throw new Exception('Record to clone not found', 400);

		$record = array_pop($records);
		unset($record[$this->primaryField]);

		// Merge new record with existing record. 
		$record = $newRecord + $record;
		$clonePrimaryID = $this->query->build($this->table)->insert($record);

		$clonedRecords = $this->query->build($this->table)->fetch($this->primaryField, $clonePrimaryID);
		$clonedRecords = $this->processRecords($clonedRecords);
		return array_pop($clonedRecords);
	}

	/**
	 * Get all records.
	 * 
	 * * Example use: $user->all()
	 * * Example use: $user->all(filters: ['Active' => 0], sortOn: ['Name' => Sort::ASC])
	 */
	public function all(array $filters = [], array $sortOn = []): null|array
	{
		$query = $this->query->build($this->table);
		if ($filters)
			$query->filterOnFields($filters);

		if ($sortOn)
			$query->sortOn($sortOn);

		$records = $query->select();
		if (!$records)
			return null;

		$records = $this->processRecords($records);

		return $records;
	}

	/**
	 * Search for records filtering on field like %value%.
	 * 
	 * * Example use: $user->search('Name', 'John', ['Name' => Sort::ASC])
	 */
	public function search(string $field, string $value, array $sortOn = []): null|array
	{
		$query = $this->query->build($this->table)
			->filterOn($field, "%$value%", Operator::LIKE);

		if ($sortOn)
			$query->sortOn($sortOn);

		$records = $query->select();
		if (!$records)
			return null;

		$records = $this->processRecords($records);

		return $records;
	}

	/**
	 * Get a record by filtering on field with value.
	 * 
	 * * Example use: $user->get('Email', 'John.doe@example.com', ['Name' => Sort::ASC])
	 */
	public function get(string $field, mixed $value, array $sortOn = []): null|array|object
	{
		$query = $this->query->build($this->table);

		if ($sortOn)
			$query->sortOn($sortOn);

		$records = $query->fetch($field, $value);
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
		$query = $this->query->build($this->table);

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
	 * * Example use: Invoice->sum('Amount')
	 * * Example use: Invoice->sum('Amount', ['Paid' => 0, 'Due' => '2024-07-25'])
	 * * Example use: Invoice->sum('Amount', groupFields: ['Paid'])
	 */
	public function sum(string $sumField, array $filters = [], array $groupByFields = []): null|array
	{
		$query = $this->query->build($this->table);

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
	 * * Example use: Invoice->avg('Amount')
	 * * Example use: Invoice->avg('Amount', ['Paid' => 0, 'Due' => '2024-07-25'])
	 * * Example use: Invoice->avg('Amount', groupFields: ['Paid'])
	 */
	public function avg(string $averageField, array $filters = [], array $groupByFields = []): null|array
	{
		$query = $this->query->build($this->table);

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
	 * * Example use: Invoice->min('Amount')
	 * * Example use: Invoice->min('Amount', ['Paid' => 0, 'Due' => '2024-07-25'])
	 * * Example use: Invoice->min('Amount', groupFields: ['Paid'])
	 */
	public function min(string $minField, array $filters = [], array $groupByFields = []): null|array
	{
		$query = $this->query->build($this->table);

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
	 * * Example use: Invoice->max('Amount')
	 * * Example use: Invoice->max('Amount', ['Paid' => 0, 'Due' => '2024-07-25'])
	 * * Example use: Invoice->max('Amount', groupFields: ['Paid'])
	 */
	public function max(string $maxField, array $filters = [], array $groupByFields = []): null|array
	{
		$query = $this->query->build($this->table);

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
	 * * Example use: $user->paginate(1, 10, ['Name' => 'John'], ['Name' => Sort::ASC])
	 */
	public function paginate(
		int $page,
		int $pageSize,
		array $filters = [],
		array $sortOn = []
	): null|array {
		$query = $this->query->build($this->table);

		if ($filters)
			$query->filterOnFields($filters);

		if ($sortOn)
			$query->sortOn($sortOn);

		$offset = ($page - 1) * $pageSize;
		$query->offset($offset)->limit($pageSize);

		$records = $query->select();
		if (!$records)
			return null;

		$records = $this->processRecords($records);

		return $records;
	}
}
