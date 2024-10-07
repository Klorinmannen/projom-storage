<?php

declare(strict_types=1);

namespace Projom\Storage\Engine;

use Projom\Storage\Action;

interface DriverInterface
{
	public function dispatch(Action $action, mixed $args): mixed;
}
