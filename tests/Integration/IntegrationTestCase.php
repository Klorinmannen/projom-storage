<?php

declare(strict_types=1);

namespace JRF\Tests\Integration;

use PHPUnit\Framework\TestCase;

use JRF\Storage\Manager;
use JRF\Storage\Database\Query;

abstract class IntegrationTestCase extends TestCase
{
    protected function setUp(): void
    {
        Manager::initialize([
            [
                'engine' => 'mysql',
                'connections' => [
                    ['dsn' => 'sqlite::memory:']
                ]
            ]
        ]);

        $this->createSchema();
        $this->seedData();
    }

    private function createSchema(): void
    {
        Query::sql(
            'CREATE TABLE User (
                UserID   INTEGER PRIMARY KEY AUTOINCREMENT,
                Username TEXT    NOT NULL,
                Password TEXT    NOT NULL,
                Firstname TEXT,
                Lastname  TEXT,
                Active    INTEGER DEFAULT 1,
                Created   DATETIME DEFAULT CURRENT_TIMESTAMP,
                Updated   DATETIME DEFAULT CURRENT_TIMESTAMP
            )'
        );

        Query::sql(
            'CREATE TABLE Role (
                RoleID      INTEGER PRIMARY KEY AUTOINCREMENT,
                Role        TEXT NOT NULL,
                Description TEXT,
                Active      INTEGER DEFAULT 1,
                Created     DATETIME DEFAULT CURRENT_TIMESTAMP,
                Updated     DATETIME DEFAULT CURRENT_TIMESTAMP
            )'
        );

        Query::sql(
            'CREATE TABLE UserRole (
                UserRoleID INTEGER PRIMARY KEY AUTOINCREMENT,
                UserID     INTEGER NOT NULL,
                RoleID     INTEGER NOT NULL,
                Active     INTEGER DEFAULT 1,
                Created    DATETIME DEFAULT CURRENT_TIMESTAMP,
                Updated    DATETIME DEFAULT CURRENT_TIMESTAMP
            )'
        );
    }

    private function seedData(): void
    {
        Query::sql(
            "INSERT INTO User (Username, Password, Firstname, Lastname, Active) VALUES
                ('system',                 'system',     NULL,     NULL,   1),
                ('john.doe@example.com',   'pass1234',   'John',   'Doe',  1),
                ('jane.doe@example.com',   'qwerty1234', 'Jane',   'Doe',  1),
                ('sofie.doe@example.com',  'asdf1234',   'Sofie',  'Doe',  0),
                ('andrew.doe@example.com', 'zxcv1234',   'Andrew', 'Doe',  0)"
        );

        Query::sql(
            "INSERT INTO Role (Role, Description) VALUES
                ('System', 'System/God user'),
                ('Admin',  'Administrator'),
                ('User',   'Regular User'),
                ('Guest',  'Guest User')"
        );

        Query::sql(
            'INSERT INTO UserRole (UserID, RoleID) VALUES
                (1, 1),
                (2, 2),
                (3, 3),
                (4, 3),
                (5, 3)'
        );
    }
}
