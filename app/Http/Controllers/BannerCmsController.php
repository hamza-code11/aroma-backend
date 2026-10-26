<?php

namespace App\Http\Controllers;

use App\Http\Requests\BannerCmsRequest;
use App\Models\BannerCms;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BannerCmsController extends Controller
{
    private const CACHE_KEY = 'banner_cms_content';

    // Public: Get banner content
    public function show(): JsonResponse
    {
        $content = Cache::remember(self::CACHE_KEY, 3600, function () {
            return BannerCms::first();
        });

        return response()->json([
            'status' => 'success',
            'data' => $content,
        ]);
    }

    // Admin: Update banner content
        public function update(BannerCmsRequest $request): JsonResponse
    {
        $content = BannerCms::firstOrCreate([], [
            'cards' => $request->cards, // Pehle se array hai
        ]);

        $data = $request->validated();

        // Image upload
        if ($request->hasFile('image')) {
            if ($content->image) {
                Storage::disk('public')->delete($content->image);
            }
            $data['image'] = $request->file('image')->store('banners', 'public');
        } else {
            unset($data['image']);
        }

        $content->update($data);
        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'message' => 'Banner updated successfully.',
            'data' => $content->fresh(),
        ]);
    }
    
}
