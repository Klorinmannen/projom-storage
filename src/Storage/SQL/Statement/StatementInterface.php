<?php

declare(strict_types=1);

namespace Projom\Storage\SQL\Statement;

interface StatementInterface
{
	public function statement(): array;
}
