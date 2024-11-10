<?php

declare(strict_types=1);

namespace Tests\Unit\Storage\Logger;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Projom\Storage\Logger\LoggerType;
use Projom\Storage\Logger\SimpleLogger;
use Psr\Log\LogLevel;

class SimpleLoggerTest extends TestCase
{
	public static function logLevelsProvider(): array
	{
		return [
			[
				'message' => 'This is a test message',
				'context' => [],
				'level' => LogLevel::DEBUG,
				'logFormat' => '[{level}] {message}',
				'expectedMessage' => '[DEBUG] This is a test message' . PHP_EOL
			],
			[
				'message' => 'This is a test message',
				'context' => [],
				'level' => LogLevel::INFO,
				'logFormat' => '[{level}] {message}',
				'expectedMessage' => '[INFO] This is a test message' . PHP_EOL
			],
			[
				'message' => 'This is a test message',
				'context' => [],
				'level' => LogLevel::NOTICE,
				'logFormat' => '[{level}] {message}',
				'expectedMessage' => '[NOTICE] This is a test message' . PHP_EOL
			],
			[
				'message' => 'This is a test message',
				'context' => [],
				'level' => LogLevel::WARNING,
				'logFormat' => '[{level}] {message}',
				'expectedMessage' => '[WARNING] This is a test message' . PHP_EOL
			],
			[
				'message' => 'This is a test message',
				'context' => [],
				'level' => LogLevel::ERROR,
				'logFormat' => '[{level}] {message}',
				'expectedMessage' => '[ERROR] This is a test message' . PHP_EOL
			],
			[
				'message' => 'This is a test message',
				'context' => [],
				'level' => LogLevel::CRITICAL,
				'logFormat' => '[{level}] {message}',
				'expectedMessage' => '[CRITICAL] This is a test message' . PHP_EOL
			],
			[
				'message' => 'This is a test message',
				'context' => [],
				'level' => LogLevel::ALERT,
				'logFormat' => '[{level}] {message}',
				'expectedMessage' => '[ALERT] This is a test message' . PHP_EOL
			],
			[
				'message' => 'This is a test message',
				'context' => [],
				'level' => LogLevel::EMERGENCY,
				'logFormat' => '[{level}] {message}',
				'expectedMessage' => '[EMERGENCY] This is a test message' . PHP_EOL
			]
		];
	}

	#[Test]
	#[DataProvider('logLevelsProvider')]
	public function logLevels(string $message, array $context, string $level, string $logFormat, string $expectedMessage): void
	{
		$logger = new SimpleLogger(LoggerType::LOG_STORE, $level);
		$logger->setLogFormat($logFormat);

		match ($level) {
			LogLevel::DEBUG => $logger->debug($message, $context),
			LogLevel::INFO => $logger->info($message, $context),
			LogLevel::NOTICE => $logger->notice($message, $context),
			LogLevel::WARNING => $logger->warning($message, $context),
			LogLevel::ERROR => $logger->error($message, $context),
			LogLevel::CRITICAL => $logger->critical($message, $context),
			LogLevel::ALERT => $logger->alert($message, $context),
			LogLevel::EMERGENCY => $logger->emergency($message, $context),
		};

		$this->assertEquals($expectedMessage, $logger->logStore());
		$this->assertCount(1, $logger->logStore(asString: false));
	}

	#[Test]
	public function logWithInvalidLevel(): void
	{
		$logger = new SimpleLogger(LoggerType::LOG_STORE, LogLevel::DEBUG);
		$this->expectExceptionMessage('Invalid log level: invalid');
		$logger->log('invalid', 'This is a test message');
	}

	#[Test]
	public function logLevelNotMet(): void
	{
		$logger = new SimpleLogger(LoggerType::LOG_STORE, LogLevel::INFO);
		$logger->debug('This is a test message');
		$this->assertCount(0, $logger->logStore(asString: false));
	}

	public static function logInterpolateProvider(): array
	{
		return [
			[
				'message' => 'This is a test message with {name}',
				'context' => ['name' => 'context'],
				'expectedMessage' => '[DEBUG] This is a test message with context' . PHP_EOL
			],
			[
				'message' => 'This is a test message with {array}',
				'context' => ['array' => ['key' => 'value']],
				'expectedMessage' => '[DEBUG] This is a test message with {"key":"value"}' . PHP_EOL
			],
			[
				'message' => 'This is a test message with {object}',
				'context' => ['object' => new class {
					public function __toString(): string
					{
						return 'object';
					}
				}],
				'expectedMessage' => '[DEBUG] This is a test message with object' . PHP_EOL
			],
			[
				'message' => 'This is a test message with {object}',
				'context' => ['object' => (object) ['key' => 'value']],
				'expectedMessage' => '[DEBUG] This is a test message with Class: stdClass.' . PHP_EOL
			],
			[
				'message' => 'This is a test message with {null}',
				'context' => ['null' => null],
				'expectedMessage' => '[DEBUG] This is a test message with null' . PHP_EOL
			],
			[
				'message' => 'This is a test message with {bool}',
				'context' => ['bool' => true],
				'expectedMessage' => '[DEBUG] This is a test message with true' . PHP_EOL
			],
			[
				'message' => 'This is a test message with {bool}',
				'context' => ['bool' => false],
				'expectedMessage' => '[DEBUG] This is a test message with false' . PHP_EOL
			]
		];
	}

	#[Test]
	#[DataProvider('logInterpolateProvider')]
	public function logInterpolate(string $message, array $context, string $expectedMessage): void
	{
		$logger = new SimpleLogger(LoggerType::LOG_STORE, LogLevel::DEBUG);
		$logger->setLogFormat('[{level}] {message}');
		$logger->debug($message, $context);
		$this->assertEquals($expectedMessage, $logger->logStore());
	}
}
