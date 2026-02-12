<?php

declare(strict_types=1);

namespace JRF\Storage\Internal\Engine\Connection;

interface ConnectionInterface
{
	public function name(): int|string;
}
