<?php

declare(strict_types=1);

namespace Projom\Tests\Unit\Storage\Engine;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Projom\Storage\Engine\Config;

class ConfigTest extends TestCase
{
	#[Test]
	public function construct(): void
	{
		$config = new Config([
			'host' => 'localhost',
			'port' => '3306',
			'username' => 'root',
			'password' => 'root',
			'database' => 'example-com'
		]);

		$this->assertEquals('localhost', $config->host);
		$this->assertEquals('3306', $config->port);
		$this->assertEquals('root', $config->username);
		$this->assertEquals('root', $config->password);
		$this->assertEquals('example-com', $config->database);
		$this->assertNull($config->driver);
		$this->assertEquals('', $config->charset);
		$this->assertEquals('', $config->collation);
		$this->assertEquals([], $config->options);
	}
}