<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShopCmsRequest;
use App\Models\ShopCms;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ShopCmsController extends Controller
{
    private const CACHE_KEY = 'shop_cms_content';

    // Public: Get shop banner content
    public function show(): JsonResponse
    {
        $content = Cache::remember(self::CACHE_KEY, 3600, function () {
            return ShopCms::first();
        });

        return response()->json([
            'status' => 'success',
            'data' => $content,
        ]);
    }

    // Admin: Update shop banner
    public function update(ShopCmsRequest $request): JsonResponse
    {
        $content = ShopCms::firstOrCreate([], [
            'eyebrow' => $request->eyebrow,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        $data = $request->validated();

        // Image upload
        if ($request->hasFile('banner_image')) {
            if ($content->banner_image) {
                Storage::disk('public')->delete($content->banner_image);
            }
            $data['banner_image'] = $request->file('banner_image')->store('shop', 'public');
        } else {
            unset($data['banner_image']);
        }

        $content->update($data);
        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'message' => 'Shop banner updated successfully.',
            'data' => $content->fresh(),
        ]);
    }
}
