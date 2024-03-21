<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\Sql;

class Column
{
	private string $raw;
	private string $columns;

	public function __construct(array|string $column)
	{
		$this->raw = $column;
		$this->columns = $this->format($column);
	}

	public function format(array|string $columns): string
    {
	    if (is_string($columns)) 
			if (strpos($columns, ',') !== false)
				$columns = explode(',', $columns);
			else
            	$columns = [ $columns ];

        $quotedColumns = array_map(
            [ Column::class, 'quote'],
            $columns
        );
       
		$quotedString = static::join($quotedColumns);
        
		return $quotedString;
    }

	public function quote(string $subject): string
	{
		switch ($subject) {
			case '*':
				return $subject;
			default:
				return "`$subject`";
		}
	}

	public function join(array $list, string $delimeter = ','): string
	{
        return implode($delimeter, $list);
    }

	public function get(): string
	{
		return $this->columns;
	}

	public function raw(): string
	{
		return $this->raw;
	}

	public static function create(array|string $column): Column
	{
		return new Column($column);
	}
}