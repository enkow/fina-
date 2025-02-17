<?php

namespace App\Exports;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CollectionExport implements FromCollection, WithHeadings
{
	public function __construct(
		private readonly array $headings,
		private readonly Collection|AnonymousResourceCollection $data
	) {
	}

	public function collection(): Collection|AnonymousResourceCollection
	{
		return $this->data;
	}

	public function headings(): array
	{
		return $this->headings;
	}
}
