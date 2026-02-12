<?php

declare(strict_types=1);

namespace JRF\Tests\Unit\Storage\Engine\Driver;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use JRF\Storage\Internal\Engine\Driver\EngineConfig;
use JRF\Storage\Internal\Engine\Driver\Engine;

class ConfigTest extends TestCase
{
	#[Test]
	public function construct(): void
	{
		$config = new EngineConfig([
			'driver' => 'mysql',
			'options' => ['return_single_record' => true],
			'connections' => [
				'default' => [
					'host' => 'localhost',
					'port' => 3306,
					'database' => 'test',
					'username' => 'root',
					'password' => 'root'
				]
			]
		]);

		$this->assertEquals(Engine::MySQL, $config->engine);
		$this->assertEquals(['return_single_record' => true], $config->options);
		$this->assertEquals(1, count($config->connections));
	}
}
