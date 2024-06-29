<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver;

interface QueryInterface
{
	public function query(): array;
}
