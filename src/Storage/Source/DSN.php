<?php

declare(strict_types=1);

namespace Projom\Storage\Source;

class DSN
{
    public static function createString(array $config): string
    {
        [$serverHost, $serverPort, $databaseName] = static::parseConfig($config);

        return static::buildDsn($serverHost, $serverPort, $databaseName);
    }

    public static function parseConfig(array $config): array
    {
        return [
            $config['server_host'],
            $config['server_port'],
            $config['database_name']
        ];
    }

    public static function buildDsn(
        string $serverHost,
        string $serverPort,
        string $databaseName
    ): string {
        return "mysql:host={$serverHost};port={$serverPort};dbname={$databaseName}";
    }
}
