<?php

declare(strict_types=1);

namespace Projom\Storage\Engine;

use Projom\Storage\Query\Action;
use Projom\Storage\Engine\Driver\ConnectionInterface;
use Projom\Storage\Query\Format;
use Projom\Storage\Query\RecordInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class DriverBase implements LoggerAwareInterface
{
	protected LoggerInterface $logger;
	protected bool $returnSingleRecord = false;

	public function __construct(LoggerInterface $logger = new NullLogger(), array $options = [])
	{
		$this->logger = $logger;
		$this->setOptions($options);
	}

	abstract public function dispatch(Action $action, mixed $args): mixed;
	abstract public function addConnection(ConnectionInterface $connection): void;
	abstract public function changeConnection(int|string $name): void;

	public function setOptions(array $options): void
	{
		$this->logger->debug(
			'Method: {method} with {options}.',
			['options' => $options, 'method' => __METHOD__]
		);

		$this->returnSingleRecord = $options['return_single_record'] ?? false;
	}

	public function setLogger(LoggerInterface $logger): void
	{
		$this->logger = $logger;
	}

	protected function processRecords(array $records, array $formatting): mixed
	{
		$this->logger->debug(
			'Method: {method} with {records}.',
			['records' => $records, 'method' => __METHOD__]
		);

		$records = $this->formatRecords($records, ...$formatting);
		$records = $this->processOptions($records);

		return $records;
	}

	protected function formatRecords(array $records, Format $format, mixed $args = null): mixed
	{
		$this->logger->debug(
			'Method: {method} with {format} and {args}.',
			['format' => $format->name, 'args' => $args, 'method' => __METHOD__]
		);

		switch ($format) {
			case Format::ARRAY:
				return $records;

			case Format::STD_CLASS:
				return array_map(fn($record) => (object) $record, $records);

			case Format::CUSTOM_OBJECT:
				$className = $args;

				if ($className === null)
					throw new \Exception('Class name not provided.', 400);
				if (!class_exists($className))
					throw new \Exception("Class: $className does not exist.", 400);
				if (!is_subclass_of($className, RecordInterface::class))
					throw new \Exception("Class: $className must implement RecordInterface.", 400);

				return array_map(fn($record) =>  $className::createFromRecord($record), $records);

			default:
				throw new \Exception("Format is not implmented.", 400);
		}
	}

	protected function processOptions(array $records): array|object
	{
		if ($this->returnSingleRecord)
			if (count($records) === 1)
				$records = $records[0];

		return $records;
	}
}
