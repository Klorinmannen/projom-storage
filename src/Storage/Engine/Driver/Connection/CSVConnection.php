<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver\Connection;

use Projom\Storage\Engine\Driver\Connection\ConnectionInterface;
use Projom\Storage\Engine\Driver\Connection\Util;

class CSVConnection implements ConnectionInterface
{
	private const DEFAULT_OPTIONS = [
		'delimiter' => ',',
		'enclosure' => '"',
		'escape' => '\\'
	];

	private readonly int|string $name;
	private readonly string $filePath;
	private readonly array $options;
	private array $fileData = [];
	private string $fileDataHash = '';

	public function __construct(int|string $name, string $filePath, array $options = [])
	{
		$this->name = $name;

		if (! Util::fileExists($filePath))
			throw new \Exception("CSV file is not readable: $filePath", 400);

		$this->filePath = $filePath;
		$this->parseOptions($options);
		$this->readFileData();
		$this->hash();
	}

	public function name(): int|string 
	{
		return $this->name;
	}

	private function parseOptions(array $options): void
	{
		$this->options[] = $options['delimiter'] ?? static::DEFAULT_OPTIONS['delimiter'];
		$this->options[] = $options['enclosure'] ?? static::DEFAULT_OPTIONS['enclosure'];
		$this->options[] = $options['escape'] ?? static::DEFAULT_OPTIONS['escape'];
	}

	private function readFileData(): void
	{
		[$delimiter, $enclosure, $escape] = $this->options;
		$file = fopen($this->filePath, 'r');
		while ($data = fgetcsv($file, null, $delimiter, $enclosure, $escape))
			$this->fileData[] = $data;
		fclose($file);
	}

	private function hash(): void
	{
		$string = implode('', $this->fileData);
		$this->fileDataHash = md5_file($string);
	}
}
