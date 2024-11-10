<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver\Connection;

interface ConnectionInterface
{
	public function name(): int|string;
}
