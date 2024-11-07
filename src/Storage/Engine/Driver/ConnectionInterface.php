<?php

declare(strict_types=1);

namespace Projom\Storage\Engine\Driver;

interface ConnectionInterface
{
	public function name(): int|string;
}
