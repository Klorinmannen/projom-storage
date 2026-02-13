<?php

declare(strict_types=1);

namespace JRF\Storage\Internal\Database\SQL\Statement;

interface StatementInterface
{
	public function statement(): array;
}
