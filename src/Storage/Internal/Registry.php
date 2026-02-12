<?php

declare(strict_types=1);

namespace JRF\Storage\Internal;

use JRF\Storage\Manager;

class Registry
{
	private static null|Manager $manager = null;
	
	public static function set(Manager $manager): void
	{
		static::$manager = $manager;
	}

	public static function get(): Manager
	{
		if (static::$manager === null)
			throw new \Exception("Manager instance not set", 400);

		return static::$manager;
	}
}