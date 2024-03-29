<?php

declare(strict_types=1);

namespace Projom\Storage\Database;

interface SourceInterface
{
	public static function create(array $config, array $options = []): SourceInterface;
	public function execute(string $sql, ?array $params = null): mixed;
	public function connect(): void;
	public function disconnect(): void;
	public function get(): object;	
}