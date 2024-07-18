<?php

declare(strict_types=1);

namespace Projom\Storage;

use Projom\Storage\Database\Engine;

class Bootstrap
{
	public static function start(array $config): void
	{
		Engine::start();
		Engine::loadDriver($config);
	}
}
