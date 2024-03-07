<?php

declare(strict_types=1);

namespace Projom\Storage\Database\Language\Sql;

class Column
{
	private string $raw;
	private string $column;

	public function __construct(array|string $column)
	{
		$this->raw = $column;
		$this->column = $this->format($column);
	}

	public function format(array|string $columnList): string
    {
        if (is_string($columnList))
            $columnList = [ $columnList ];

        $quotedColumnList = array_map(
            [ Column::class, 'quote'],
            $columnList
        );
       
		$quotedString = static::join($quotedColumnList);
        
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
		return $this->column;
	}

	public function raw(): string
	{
		return $this->raw;
	}
}