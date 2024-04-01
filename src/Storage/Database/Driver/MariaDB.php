<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Driver;

use Projom\Storage\Database\Drivers;
use Projom\Storage\Database\PDO\Source;

class MariaDB extends MySQL 
{
	public function __construct(Source $source)
	{
		parent::__construct($source);
		$this->driver = Drivers::MariaDB;
	}
}
