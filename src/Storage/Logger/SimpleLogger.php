<?php

declare(strict_types=1);

namespace Projom\Storage\Logger;

use Stringable;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

class SimpleLogger extends AbstractLogger
{
	const RFC5424_LEVELS = [
		LogLevel::EMERGENCY => 0,
		LogLevel::ALERT => 1,
		LogLevel::CRITICAL => 2,
		LogLevel::ERROR => 3,
		LogLevel::WARNING => 4,
		LogLevel::NOTICE => 5,
		LogLevel::INFO => 6,
		LogLevel::DEBUG => 7,
	];

	private readonly string $absoluteFilePath;
	private readonly string $logLevel;
	private readonly LoggerType $type;

	public function __construct(
		string $type = LoggerType::ERROR_LOG,
		string $logLevel = LogLevel::DEBUG,
		string $absoluteFilePath = ''
	) {

		if ($type === LoggerType::FILE)
			if (!file_exists($absoluteFilePath))
				throw new InvalidArgumentException("File: $absoluteFilePath does not exist.");

		$this->validateLevel($logLevel);

		$this->absoluteFilePath = $absoluteFilePath;
		$this->logLevel = $logLevel;
		$this->type = $type;
	}

	public function log($level, string|Stringable $message, array $context = []): void
	{
		$this->validateLevel($level);

		$level = strtolower($level);
		if (static::RFC5424_LEVELS[$level] > static::RFC5424_LEVELS[$this->logLevel])
			return;

		$message = $this->interpolate($message, $context);
		$line = $this->createLine($level, $message);
		$this->writeLine($line);
	}

	private function validateLevel(string $level): void
	{
		$level = strtolower($level);
		if (!array_key_exists($level, static::RFC5424_LEVELS))
			throw new InvalidArgumentException("Invalid log level: $level");
	}

	private function interpolate(string $message, array $context): string
	{
		$replace = [];
		foreach ($context as $key => $val) {

			$val = match (true) {
				is_null($val) => 'null',
				is_array($val) => json_encode($val),
				$this->isException($key, $val) => $this->formatException($val),
				is_object($val) => $this->formatObject($val),
				default => (string) $val,
			};

			$key = '{' . $key . '}';
			$replace[$key] = $val;
		}

		return strtr($message, $replace);
	}

	private function isException(string $key, mixed $exception): bool
	{
		if ($key !== 'exception')
			return false;
		if (!is_subclass_of($exception, \Throwable::class))
			return false;
		return true;
	}

	private function formatObject(object $object): string
	{
		if ($object instanceof Stringable || method_exists($object, '__toString'))
			return (string) $object;

		$class = get_class($object);
		return "Class: $class.";
	}

	private function formatException(\Throwable $exception): string
	{
		$trace = $exception->getTraceAsString();
		$code = $exception->getCode();
		$message = $exception->getMessage();
		$file = $exception->getFile();
		$line = $exception->getLine();
		return "Exception \"$message\" with code $code in \"$file\" on line $line.\nStack trace:\n$trace";
	}

	private function createLine(string $level, string $message): string
	{
		$message = trim($message);
		$level = strtoupper($level);
		$dateTime = date('Y-m-d H:i:s');
		return "[$dateTime] [$level] $message" . PHP_EOL;
	}

	private function writeLine(string $line): void
	{
		match ($this->type) {
			LoggerType::ERROR_LOG => $this->writeToErrorLog($line),
			LoggerType::FILE => $this->writeLineToFile($line),
			default => throw new InvalidArgumentException("Invalid logger type: {$this->type}", 400),
		};
	}

	private function writeToErrorLog(string $line): void
	{
		error_log($line, message_type: 0);
	}

	private function writeLineToFile(string $line): void
	{
		file_put_contents($this->absoluteFilePath, $line, FILE_APPEND | LOCK_EX);
	}
}
