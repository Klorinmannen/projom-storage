<?php

declare(strict_types=1);

namespace Projom\Storage;

use Stringable;

use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class FileLogger implements LoggerInterface
{
	const RFC5424 = [
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

	/**
	 * Creates a new instance of FileLogger.
	 * Checks if the directory of the file is writeable.
	 * The file will be created if it does not exist yet.
	 */
	public function __construct(string $absoluteFilePath, string $logLevel)
	{
		$dir = dirname($absoluteFilePath);
		if (!is_writable($dir))
			throw new InvalidArgumentException("Directory $dir is not writable.");

		if (!file_exists($absoluteFilePath))
			if (!touch($absoluteFilePath))
				throw new InvalidArgumentException("Could not create file $absoluteFilePath.");

		$this->validateLevel($logLevel);

		$this->absoluteFilePath = $absoluteFilePath;
		$this->logLevel = $logLevel;
	}

	private function validateLevel(string $level): void
	{
		$level = strtolower($level);
		if (!array_key_exists($level, static::RFC5424))
			throw new InvalidArgumentException("Invalid log level: $level");
	}

	public function emergency(string|Stringable $message, array $context = []): void
	{
		$this->log(LogLevel::EMERGENCY, $message, $context);
	}

	public function alert(string|Stringable $message, array $context = []): void
	{
		$this->log(LogLevel::ALERT, $message, $context);
	}

	public function critical(string|Stringable $message, array $context = []): void
	{
		$this->log(LogLevel::CRITICAL, $message, $context);
	}

	public function error(string|Stringable $message, array $context = []): void
	{
		$this->log(LogLevel::ERROR, $message, $context);
	}

	public function warning(string|Stringable $message, array $context = []): void
	{
		$this->log(LogLevel::WARNING, $message, $context);
	}

	public function notice(string|Stringable $message, array $context = []): void
	{
		$this->log(LogLevel::NOTICE, $message, $context);
	}

	public function info(string|Stringable $message, array $context = []): void
	{
		$this->log(LogLevel::INFO, $message, $context);
	}

	public function debug(string|Stringable $message, array $context = []): void
	{
		$this->log(LogLevel::DEBUG, $message);
	}

	public function log($level, string|Stringable $message, array $context = []): void
	{
		$this->validateLevel($level);

		$level = strtolower($level);
		if (static::RFC5424[$level] > static::RFC5424[$this->logLevel])
			return;

		$message = $this->interpolate($message, $context);
		$line = $this->createLine($level, $message);
		$this->writeLineToFile($line);
	}

	private function interpolate(string $message, array $context): string
	{
		$replace = [];
		foreach ($context as $key => $val) {

			$val = match (true) {
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
		if ($object instanceof Stringable)
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
		return "Exception: $message with code $code in $file on line $line.\nStack trace:\n$trace";
	}

	private function createLine(string $level, string $message): string
	{
		$dateTime = date('Y-m-d H:i:s');
		return "[$dateTime] [$level] Message: $message" . PHP_EOL;
	}

	private function writeLineToFile(string $line): void
	{
		file_put_contents($this->absoluteFilePath, $line, FILE_APPEND | LOCK_EX);
	}
}
