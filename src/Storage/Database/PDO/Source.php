<?php

declare(strict_types=1);

namespace Projom\Storage\Database\PDO;

use PDO;
use Exception;

use Projom\Storage\Database\PDO\DSN;
use Projom\Storage\Database\SourceInterface;

class Source implements SourceInterface
{
	use DSN;

	private PDO|null $pdo = null;
	private array $config = [];
	private array $options = [];

	const DEFAULT_PDO_OPTIONS = [
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
	];

	public function __construct(array $config, array $options)
	{
		$this->config = $config;
		$this->options = $options;
	}

	public static function create(array $config, array $options = []): Source
	{
		return new Source($config, $options);
	}

	public function connect(): void
	{
		if ($this->pdo !== null)
			return;

		if (empty($this->config))
			throw new Exception('Missing source configuration', 400);

		$this->pdo = new PDO(
			static::DSN($this->config),
			$config['username'] ?? null,
			$config['password'] ?? null,
			$this->options + static::DEFAULT_PDO_OPTIONS
		);
	}

	public function disconnect(): void
	{
		$this->pdo = null;
	}

	public function get(): object
	{
		return $this->pdo;
	}

	public function execute(string $query, ?array $params = null): array
	{
		if ($this->pdo === null)
			$this->connect();

		if (!$statement = $this->pdo->prepare($query))
			throw new Exception('Failed to prepare PDO query', 500);

		if (!$statement->execute($params))
			throw new Exception('Failed to execute PDO query', 500);

		$result = $statement->fetchAll();
		if ($result === false)
			throw new Exception('Failed to fetch PDO query result', 500);

		return $result;
	}
}
