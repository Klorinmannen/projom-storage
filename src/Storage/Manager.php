<?php

declare(strict_types=1);

namespace JRF\Storage;

use JRF\Storage\Internal\Database\Action;
use JRF\Storage\Internal\Engine\EngineBase;
use JRF\Storage\Internal\Engine\EngineConfig;
use JRF\Storage\Internal\Engine\EngineType;
use JRF\Storage\Internal\Engine\EngineFactory;
use JRF\Storage\Internal\Engine\Connection\ConnectionFactory;
use JRF\Storage\Internal\Registry;

class Manager
{
	protected array $engines = [];
	protected null|EngineType $currentEngine = null;

	public static function initialize(array $config): Manager
	{
		$manager = new Manager();
		$connectionFactory = ConnectionFactory::create();
		$engineFactory = EngineFactory::create($connectionFactory);

		foreach ($config as $engineConfiguration) {
			$engineConfig = new EngineConfig($engineConfiguration);
			$engine = $engineFactory->createEngine($engineConfig);
			$manager->setEngine($engine, $engineConfig->engine);
		}

		Registry::set($manager);

		return $manager;
	}

	private function setEngine(EngineBase $engine, EngineType $engineType): void
	{
		$this->engines[$engineType->value] = $engine;
		$this->currentEngine = $engineType;
	}

	public function dispatch(Action $action, null|EngineType $engineType = null, mixed $args = null): mixed
	{
		if ($engineType !== null)
			$this->useEngine($engineType);

		$engine = $this->currentEngine();
		$result = $engine->dispatch($action, $args);

		return $result;
	}

	private function currentEngine(): EngineBase
	{
		$engine = $this->engines[$this->currentEngine->value];
		return $engine;
	}

	public function useEngine(EngineType $engineType): void
	{
		if ($this->currentEngine === $engineType)
			return;

		if (!array_key_exists($engineType->value,  $this->engines))
			throw new \Exception('Engine is not initialized.', 400);

		$this->currentEngine = $engineType;
	}
}
