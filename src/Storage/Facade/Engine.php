<?php

declare(strict_types=1);

namespace Projom\Storage\Facade;

use Projom\Storage\Engine as EngineObject;
use Projom\Storage\Query\Action;
use Projom\Storage\Engine\Driver\Driver;

class Engine
{
	private static null|EngineObject $instance = null;

	public static function setInstance(EngineObject $instance): void
	{
		static::$instance = $instance;
	}

	public static function dispatch(Action $action, null|Driver $driver = null, mixed $args = null): mixed
	{
		if (static::$instance === null)
			throw new \Exception("Engine instance not set", 400);

		return static::$instance->dispatch($action, $driver, $args);
	}

	public static function useDriver(Driver $driver): void
	{
		if (static::$instance === null)
			throw new \Exception("Engine instance not set", 400);

		static::$instance->useDriver($driver);
	}

	public static function reset(): void
	{
		static::$instance = null;
	}
}
