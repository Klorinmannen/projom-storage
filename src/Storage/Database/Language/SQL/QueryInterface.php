<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\SQL;

interface QueryInterface
{
	public function query(): array;
}
