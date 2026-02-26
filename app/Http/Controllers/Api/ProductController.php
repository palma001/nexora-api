<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Requests\Api\StoreProductRequest;
use App\Http\Requests\Api\UpdateProductRequest;
use App\Http\Resources\Api\ProductResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Exports\ProductsTemplate;
use App\Imports\ProductsImport;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::with(['category', 'image'])
            ->filters($request->only(['search', 'barcode', 'category_id', 'sort']))
            ->paginate($request->per_page ?? 15)
            ->withQueryString();

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);

        return DB::transaction(function() use ($request) {
            $product = Product::create($request->validated());

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $product->attachments()->create([
                    'path' => $path,
                    'disk' => 'public',
                    'original_name' => $request->file('image')->getClientOriginalName(),
                    'mime_type' => $request->file('image')->getMimeType(),
                    'size' => $request->file('image')->getSize(),
                ]);
            }

            return new ProductResource($product->load('image'));
        });
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);
        return new ProductResource($product->load(['category', 'image']));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        return DB::transaction(function() use ($request, $product) {
            $product->update($request->validated());

            if ($request->hasFile('image')) {
                // Delete old images
                foreach ($product->attachments as $old) {
                    Storage::disk($old->disk)->delete($old->path);
                    $old->delete();
                }

                $path = $request->file('image')->store('products', 'public');
                $product->attachments()->create([
                    'path' => $path,
                    'disk' => 'public',
                    'original_name' => $request->file('image')->getClientOriginalName(),
                    'mime_type' => $request->file('image')->getMimeType(),
                    'size' => $request->file('image')->getSize(),
                ]);
            }

            return new ProductResource($product->load('image'));
        });
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->delete();
        return response()->json(null, 204);
    }

    public function export()
    {
        $this->authorize('viewAny', Product::class);
        return Excel::download(new ProductsExport, 'productos.xlsx');
    }

    public function template()
    {
        $this->authorize('viewAny', Product::class);
        return Excel::download(new ProductsTemplate, 'plantilla_productos.xlsx');
    }

    public function import(Request $request)
    {
        $this->authorize('create', Product::class);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new ProductsImport, $request->file('file'));

        return response()->json(['message' => 'Productos importados correctamente']);
    }
}
