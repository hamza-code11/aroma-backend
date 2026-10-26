<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    private const CACHE_KEY = 'products_list';

    // Get all products (Cached)
    public function index(): JsonResponse
    {
        $products = Cache::remember(self::CACHE_KEY, 3600, function () {
            return Product::with('category:id,name')
                ->select(
                    'id',
                    'category_id',
                    'name',
                    'slug',
                    'sku',
                    'description',
                    'regular_price',
                    'sale_price',
                    'stock_quantity',
                    'weight',
                    'weight_unit',
                    'length',
                    'width',
                    'height',
                    'dimension_unit',
                    'shipping_class',
                    'main_image',
                    'images',
                    'is_active',
                    'created_at'
                )
                ->orderBy('id', 'desc')
                ->get();
        });

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ], 200);
    }

    // Get single product
    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $product->load('category:id,name'),
        ], 200);
    }

    // Create product
    public function store(ProductRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['slug'] = $this->makeUniqueSlug($data['slug'] ?? $data['name']);

        $data['main_image'] = $request->file('main_image')
            ->store('products', 'public');

        $data['images'] = [];

        foreach ($request->file('images', []) ?? [] as $image) {
            $data['images'][] = $image->store('products', 'public');
        }

        $data['shipping_class'] = $data['shipping_class'] ?? 'standard';
        $data['is_active'] = $request->boolean('is_active', true);

        $product = Product::create($data);

        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully.',
            'data' => $product,
        ], 201);
    }







    // Update product
        public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();

        if (!empty($data['slug'])) {
            $data['slug'] = $this->makeUniqueSlug($data['slug'], $product->id);
        } else {
            unset($data['slug']);
        }

        $data['shipping_class'] = $data['shipping_class'] ?? 'standard';

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        // Main image update logic
        if ($request->hasFile('main_image')) {
            // 1. Nayi file upload hui hai
            $newImage = $request->file('main_image')->store('products', 'public');

            if ($product->main_image) {
                Storage::disk('public')->delete($product->main_image);
            }

            $data['main_image'] = $newImage;
        } 
        elseif ($request->has('main_image') && !is_null($request->main_image) && $request->main_image !== '') {
            // 2. User ne kisi purani image ko "Main" set kiya hai (string path aya hai)
            $data['main_image'] = $request->main_image;
        }

        // Thumbnails (Gallery) Update Logic
        if ($request->hasFile('images')) {
            $newImages = [];

            foreach ($request->file('images') as $image) {
                $newImages[] = $image->store('products', 'public');
            }

            // Purani images ko delete karein (jo ab gallery mein nahi hain)
            if ($product->images) {
                foreach ($product->images as $oldImage) {
                    if (!in_array($oldImage, $newImages)) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }
            }

            $data['images'] = $newImages;
        }

        $product->update($data);
        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully.',
            'data' => $product->fresh()->load('category:id,name'),
        ], 200);
    }

    

    // Delete product
    public function destroy(Product $product): JsonResponse
    {
        if ($product->main_image) {
            Storage::disk('public')->delete($product->main_image);
        }

        foreach ($product->images ?? [] as $image) {
            Storage::disk('public')->delete($image);
        }

        $product->delete();

        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully.',
        ], 200);
    }

    /**
     * Toggle product active status.
     */
    public function toggleStatus(Product $product): JsonResponse
    {
        $product->update([
            'is_active' => !$product->is_active,
        ]);

        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'message' => 'Product status updated.',
            'data' => $product,
        ], 200);
    }

    /**
     * Unique slug banata hai (name-1, name-2 ...).
     */
    private function makeUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $slug = $base;
        $i = 1;

        while (
            Product::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}

