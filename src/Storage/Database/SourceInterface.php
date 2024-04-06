<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

interface SourceInterface
{
	public function execute(string $sql, ?array $params = null): mixed;
	public function get(): object;	
}