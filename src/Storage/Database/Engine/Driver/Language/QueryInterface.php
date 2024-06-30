<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Engine\Driver\Language;

interface QueryInterface
{
	public function query(): array;
}
