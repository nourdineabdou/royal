<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GenericExport implements FromCollection, WithHeadings
{
    protected $data;
    protected $headings;

    public function __construct($data)
    {
        $this->data = $data;
        $this->headings = $data->isNotEmpty() ? array_keys($data->first()) : [];
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
