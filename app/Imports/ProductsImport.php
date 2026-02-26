<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Attachment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['nombre'])) {
            return null;
        }

        // 1. Find or create Category
        $categoryName = $row['categoria'] ?? 'Sin Categoría';
        $category = Category::firstOrCreate(['name' => $categoryName]);

        // 2. Find or create Product
        $product = Product::updateOrCreate(
            ['barcode' => $row['codigo_barras'] ?? null],
            [
                'name' => $row['nombre'],
                'description' => $row['descripcion'] ?? null,
                'price' => $row['precio'] ?? 0,
                'cost' => $row['costo'] ?? 0,
                'stock' => $row['stock'] ?? 0,
                'stock_min' => $row['stock_minimo'] ?? 0,
                'category_id' => $category->id,
            ]
        );

        // 3. Handle Image URL if present
        $imageUrl = $row['url_imagen'] ?? null;
        if ($imageUrl && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            try {
                $response = Http::get($imageUrl);
                if ($response->successful()) {
                    $contents = $response->body();
                    $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                    $filename = 'products/' . Str::random(20) . '.' . $extension;
                    
                    Storage::disk('public')->put($filename, $contents);

                    // Delete old images
                    foreach ($product->attachments as $old) {
                        Storage::disk($old->disk)->delete($old->path);
                        $old->delete();
                    }

                    $product->attachments()->create([
                        'path' => $filename,
                        'disk' => 'public',
                        'original_name' => basename($imageUrl),
                        'mime_type' => $response->header('Content-Type'),
                        'size' => strlen($contents),
                    ]);
                }
            } catch (\Exception $e) {
                // Skip image if download fails
            }
        }

        return $product;
    }
}
