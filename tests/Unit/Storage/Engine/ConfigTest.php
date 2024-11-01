<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Engine;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine\Config;
use Projom\Storage\Engine\Driver;

class ConfigTest extends TestCase
{
	#[Test]
	public function construct(): void
	{
		$config = new Config([
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

		$this->assertEquals(Driver::MySQL, $config->driver);
		$this->assertEquals(['return_single_record' => true], $config->options);
		$this->assertEquals(1, count($config->connections));
	}
}
