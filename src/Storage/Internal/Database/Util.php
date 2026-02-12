<?php

declare(strict_types=1);

namespace JRF\Storage\Internal\Database;

use JRF\Storage\Internal\Util as InternalUtil;

class Util extends InternalUtil
{
	private const REDACTED = '__REDACTED__';

	public static function processRecords(array $records, array $options): array
	{
		$processedRecords = [];
		foreach ($records as $key => $record) {

			if ($selectFields = $options['select_fields'] ?? [])
				$record = Util::selectRecordFields($record, $selectFields);

			if ($formatFields = $options['format_fields'] ?? [])
				$record = Util::formatRecord($record, $formatFields);

			if ($redactFields = $options['redact_fields'] ?? [])
				$record = Util::redactRecord($record, $redactFields, static::REDACTED);

			if ($translateFields = $options['translate_fields'] ?? [])
				$record = Util::translateRecordFields($record, $translateFields);

			$processedRecords[$key] = $record;
		}

		if ($primaryField = $options['rekey_with_primary_field'] ?? '')
			$processedRecords = Util::rekey($processedRecords, $primaryField);

		return $processedRecords;
	}

	public static function selectRecordFields(array $record, array $selectFields): array
	{
		if (!$selectFields)
			return $record;

		$modifiedRecord = [];
		foreach ($selectFields as $field)
			if (array_key_exists($field, $record))
				$modifiedRecord[$field] = $record[$field];

		return $modifiedRecord;
	}

	public static function formatRecord(array $record, array $formatFields): array
	{
		if (!$formatFields)
			return $record;

		foreach ($formatFields as $field => $type) {
			if (!array_key_exists($field, $record))
				continue;
			$value = $record[$field];
			$record[$field] = static::format($value, $type);
		}

		return $record;
	}

	public static function format(mixed $value, string $type): mixed
	{
		$type = strtolower($type);
		return match ($type) {
			'int' => (int) $value,
			'float' => (float) $value,
			'bool' => (bool) $value,
			'date' => date('Y-m-d', strtotime((string) $value)),
			'datetime' => date('Y-m-d H:i:s', strtotime((string) $value)),
			'string' => (string) $value,
			default => $value,
		};
	}

	public static function redactRecord(array $record, array $redactedFields, string $redactText): array
	{
		if (!$redactedFields)
			return $record;

		foreach ($redactedFields as $field)
			if (array_key_exists($field, $record))
				$record[$field] = $redactText;

		return $record;
	}

	public static function translateRecordFields(array $record, array $translateFields): array
	{
		if (!$translateFields)
			return $record;

		$translatedRecord = [];
		foreach ($translateFields as $field => $translatedField)
			if (array_key_exists($field, $record))
				$translatedRecord[$translatedField] = $record[$field];

		return $translatedRecord;
	}

	public static function dynamicPrimaryField(string $calledClass, bool $useNamespaceAsTableName): string
	{
		$table = static::dynamicTableName($calledClass, $useNamespaceAsTableName);
		return $table . 'ID';
	}

	public static function dynamicTableName(string $calledClass, bool $useNamespaceAsTableName): string
	{
		if ($useNamespaceAsTableName)
			return Util::tableFromNamespace($calledClass);
		return Util::tableFromCalledClass($calledClass);
	}

	public static function tableFromNamespace(string $calledClass): string
	{
		$parts = explode('\\', $calledClass);
		array_shift($parts); // Remove the first namespace part, App .. w/e.
		array_pop($parts); // Removes the last part, which is the class name.
		return implode('', array_map('ucfirst', $parts));
	}

	public static function tableFromCalledClass(string $calledClass): string
	{
		$calledClass = str_replace('\\', DIRECTORY_SEPARATOR, $calledClass);
		$class = basename($calledClass);
		return static::replace($class, ['Repository', 'Repo']);
	}

	public static function replace(string $string, array $replace, string $replaceWith = ''): string
	{
		return str_ireplace($replace, $replaceWith, $string);
	}
}
