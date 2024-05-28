<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\Driver\QueryInterface;

interface SourceInterface
{
	public function run(QueryInterface $queryAble): mixed;
	public function execute(string $sql, array|null $params): mixed;
	public function get(): object;	
}