<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\CategoryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    // Cache key constant
    private const CACHE_KEY = 'categories_list';

    /**
     * Get all categories (Cached for high performance)
     */
    public function index(): JsonResponse
    {
        // 1 ghante (3600 seconds) ke liye cache karega
        $categories = Cache::remember(self::CACHE_KEY, 3600, function () {
            // Sirf zaroori columns select kiye hain (Performance optimization)
            return Category::select('id', 'name', 'slug', 'is_active')
                ->orderBy('id', 'desc')
                ->get();
        });

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ], 200);
    }

    /**
     * Store a newly created category
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        // Naya data aane par purana cache clear karein
        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully.',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified category
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $category
        ], 200);
    }

    /**
     * Update the specified category
     */
    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        // Data update hone par cache clear karein
        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully.',
            'data' => $category
        ], 200);
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        // Data delete hone par cache clear karein
        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully.'
        ], 200);
    }







        /**
     * Toggle category active status.
     */
    public function toggleStatus(Category $category): JsonResponse
    {
        // Current status ko ulta kar dein (true to false, false to true)
        $category->update([
            'is_active' => !$category->is_active
        ]);

        // Cache clear karein
        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'message' => 'Category status updated.',
            'data' => $category
        ], 200);
    }



    
}