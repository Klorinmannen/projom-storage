# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Install dependencies
composer install

# Run unit tests
./vendor/bin/phpunit --testsuite="Unit"

# Run a single test file
./vendor/bin/phpunit tests/Unit/Path/To/SomeTest.php

# Run a single test method
./vendor/bin/phpunit --filter testMethodName

# Run integration tests (requires MySQL with user "JRF" / password "JRF")
./vendor/bin/phpunit --testsuite="Integration"

# Static analysis
./vendor/bin/psalm

# Taint analysis (security)
./vendor/bin/psalm --taint-analysis
```

Psalm runs at error level 4 (strictest). All code must pass static analysis.

## Architecture

JRF Storage is a framework-agnostic PHP 8.2+ library providing a MySQL query builder and repository pattern for data access.

### Entry Points

- **`Manager`** — Singleton initialized once with a config array (`driver`, `connections`). Stored in `Registry`. All query dispatch flows through here.
- **`Query`** — Static facade. `Query::build('TableName')` returns a fluent `Builder`. Also exposes `Query::sql()` for raw SQL, `Query::startTransaction()`, and `Query::useConnection('name')` to switch connections.
- **`Repository` trait** — Applied to model classes alongside a `table()` and `primaryField()` method. Provides `find()`, `all()`, `create()`, `update()`, `delete()`, `count()`, `sum()`, `paginate()`.

### Request Flow

```
Query::build('Users')
  └── Manager (dispatch)
       └── Engine (MySQL)
            └── Statement (SQL executor)
                 └── Builder (fluent clause builder)
                      └── SQL Component classes (Filter, Join, Order, …)
```

### Key Directories

- `src/Manager.php` — top-level singleton dispatcher
- `src/Database/` — public API: `Query` facade, `Repository` trait, `Util/` enums (Operator, Sort, Filter, etc.)
- `src/Internal/Engine/` — `MySQL.php` (concrete), `EngineBase.php` (abstract), `EngineFactory.php`, `EngineType` enum
- `src/Internal/Engine/Connection/` — PDO wrapper, DSN builder, connection pooling by name
- `src/Internal/Database/SQL/` — `Statement.php` (executes), `Statement/Builder.php` (fluent API), `Statement/` (Select/Insert/Update/Delete), `Component/` (individual clause builders)

### Repository Output Processing

`EngineBase` applies a pipeline to query results controlled by methods on the Repository class:

- `selectFields()` — whitelist returned fields
- `formatFields()` — type-cast fields
- `redactFields()` — remove sensitive fields from output
- `translateFields()` — rename fields on output
- `rekeyWithPrimaryField()` — reindex result array by primary key
- Return format controlled by `Format` enum: `ARRAY` (default), `STD_CLASS`, `CUSTOM_OBJECT`

### Integration Test Setup

Integration tests need a MySQL instance. The fixture database/user are created by `tests/Integration/setup.sh` and seeded with `tests/Integration/data.sql`. The test fixtures live in `UserRepository.php` and `UserRecord.php`.
