<?php

declare(strict_types=1);

namespace Projom\Storage\Engine;


use Projom\Storage\Action;
use Projom\Storage\Engine\Driver\ConnectionInterface;
use Projom\Storage\Format;
use Projom\Storage\RecordInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

abstract class DriverBase implements LoggerAwareInterface
{
	protected null|LoggerInterface $logger = null;
	protected bool $returnSingleRecord = false;

	abstract public function dispatch(Action $action, mixed $args): mixed;
	abstract public function setConnection(ConnectionInterface $connection, int|string $name): void;
	abstract public function changeConnection(int|string $name): void;

	public function setLogger(LoggerInterface $logger): void
	{
		$this->logger = $logger;
	}

	public function setOptions(array $options): void
	{
		$this->log(
			LogLevel::DEBUG,
			'Setting options: {options} in {method}.',
			['options' => $options, 'method' => __METHOD__]
		);

		$this->returnSingleRecord = $options['return_single_record'] ?? false;
	}

	protected function formatRecords(array $records, Format $format, mixed $args = null): mixed
	{
		$this->log(
			LogLevel::DEBUG,
			'Formatting records with {format} and args {args} in {method}.',
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

	protected function log(string $level, string $message, array $context = []): void
	{
		if ($this->logger === null)
			return;

		$this->logger->log($level, $message, $context);
	}
}
