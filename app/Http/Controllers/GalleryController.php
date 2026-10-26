<?php

namespace App\Http\Controllers;

use App\Http\Requests\GalleryRequest;
use App\Models\Gallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GalleryController extends Controller
{
    private const CACHE_KEY = 'gallery_list';

    // Public: get gallery images
    public function index(): JsonResponse
    {
        $gallery = Cache::remember(
            self::CACHE_KEY,
            3600,
            fn () => Gallery::latest()->paginate(20)
        );

        return response()->json([
            'status' => 'success',
            'data' => $gallery,
        ]);
    }

    // Admin: upload image
    public function store(GalleryRequest $request): JsonResponse
    {
        $path = $request->file('image')->store('gallery', 'public');

        try {
            $gallery = Gallery::create([
                'image' => $path,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }

        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'message' => 'Gallery image uploaded successfully.',
            'data' => $gallery,
        ], 201);
    }

    // Admin: replace image
    public function update(
        GalleryRequest $request,
        Gallery $gallery
    ): JsonResponse {
        $newPath = $request->file('image')->store('gallery', 'public');
        $oldPath = $gallery->image;

        try {
            DB::transaction(function () use ($gallery, $newPath) {
                $gallery->update([
                    'image' => $newPath,
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newPath);

            throw $exception;
        }

        Cache::forget(self::CACHE_KEY);

        // Delete old file after database update succeeds
        Storage::disk('public')->delete($oldPath);

        return response()->json([
            'status' => 'success',
            'message' => 'Gallery image updated successfully.',
            'data' => $gallery->fresh(),
        ]);
    }

    // Admin: delete image
    public function destroy(Gallery $gallery): JsonResponse
    {
        $path = $gallery->image;

        $gallery->delete();

        Cache::forget(self::CACHE_KEY);

        Storage::disk('public')->delete($path);

        return response()->json([
            'status' => 'success',
            'message' => 'Gallery image deleted successfully.',
        ]);
    }

    
}
