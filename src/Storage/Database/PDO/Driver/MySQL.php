<?php

declare(strict_types=1);

namespace Projom\Storage\Database\PDO\Driver;

use Projom\Storage\Database\DriverInterface;
use Projom\Storage\Database\Language\SQL;
use Projom\Storage\Database\PDO\Source;

class MySQL implements DriverInterface
{
	use Source, SQL;

	public function __construct(array $config)
	{
		$this->connect($config);
	}
}
