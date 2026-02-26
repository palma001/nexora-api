<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsTemplate implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect([]);
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Descripcion',
            'Precio',
            'Costo',
            'Codigo_Barras',
            'Stock',
            'Stock_Minimo',
            'Categoria',
            'URL_Imagen',
        ];
    }
}
