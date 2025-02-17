<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ArrayExport implements FromArray, WithHeadings
{
	public function __construct(private readonly array $headings, private readonly array $data)
	{
	}

	public function array(): array
	{
		return $this->data;
	}

	public function headings(): array
	{
		return $this->headings;
	}
}
