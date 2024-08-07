<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine;

use Projom\Storage\Database\Query\Action;

interface DriverInterface
{
	public function dispatch(Action $action, mixed $args): mixed;
	public function startTransaction(): void;
	public function endTransaction(): void;
	public function revertTransaction(): void;
}
