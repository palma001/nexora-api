<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Product::with(['category', 'image'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
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

    /**
    * @var Product $product
    */
    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->description,
            $product->price,
            $product->cost,
            $product->barcode,
            $product->stock,
            $product->stock_min,
            $product->category ? $product->category->name : '',
            $product->image ? $product->image->url : '',
        ];
    }
}
