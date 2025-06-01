<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver\Connection;

use Projom\Storage\Util as StorageUtil;

class Util extends StorageUtil
{
	public static function fileExists(string $filePath): bool
	{
		if (! file_exists($filePath))
			return false;
		if (! is_file($filePath))
			return false;
		if (! is_readable($filePath))
			return false;
		return true;
	}

	public static function readCSV(
		string $filePath,
		string $delimiter,
		string $enclosure,
		string $escape
	): array {

		if (! static::fileExists($filePath))
			throw new \Exception("CSV file: $filePath, is not readable.", 400);

		$fileData = [];

		$file = fopen($filePath, 'r');
		while ($line = fgetcsv($file, null, $delimiter, $enclosure, $escape))
			$fileData[] = $line;
		fclose($file);

		return $fileData;
	}

	public static function writeCSV(
		string $filePath,
		array $fileData,
		string $delimiter,
		string $enclosure,
		string $escape
	): void {

		if (! static::fileExists($filePath))
			throw new \Exception("CSV file: $filePath, is not writable.", 400);

		$file = fopen($filePath, 'w');
		foreach ($fileData as $line)
			fputcsv($file, $line, $delimiter, $enclosure, $escape);
		fclose($file);
	}
}
