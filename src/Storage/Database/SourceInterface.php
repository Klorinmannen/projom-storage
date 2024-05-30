<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

use Projom\Storage\Database\Driver\QueryInterface;

interface SourceInterface
{
	public function run(QueryInterface $queryAble): void;
	public function execute(string $sql, array|null $params): void;
	public function fetchResult(): array;
	public function get(): object;	
}