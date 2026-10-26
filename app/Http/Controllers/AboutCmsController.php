<?php

namespace App\Http\Controllers;

use App\Http\Requests\AboutCmsRequest;
use App\Models\AboutCms;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AboutCmsController extends Controller
{
    private const CACHE_KEY = 'about_cms_content';

    // Public: Get about content
    public function show(): JsonResponse
    {
        $content = Cache::remember(self::CACHE_KEY, 3600, function () {
            return AboutCms::first();
        });

        return response()->json([
            'status' => 'success',
            'data' => $content,
        ]);
    }

    // Admin: Update content
    public function update(AboutCmsRequest $request): JsonResponse
    {
        $content = AboutCms::firstOrCreate([], [
            'hero' => $request->hero,
            'stats' => $request->stats,
            'story' => $request->story,
            'mission_vision' => $request->mission_vision,
            'cta' => $request->cta,
        ]);

        $data = $request->validated();

        // JSON strings ko arrays mein decode karein (FormData se aane par)
        if (is_string($request->hero)) {
            $data['hero'] = json_decode($request->hero, true);
        }
        if (is_string($request->stats)) {
            $data['stats'] = json_decode($request->stats, true);
        }
        if (is_string($request->story)) {
            $data['story'] = json_decode($request->story, true);
        }
        if (is_string($request->mission_vision)) {
            $data['mission_vision'] = json_decode($request->mission_vision, true);
        }
        if (is_string($request->cta)) {
            $data['cta'] = json_decode($request->cta, true);
        }

        // Main Banner Image
        if ($request->hasFile('banner_image')) {
            if ($content->banner_image) {
                Storage::disk('public')->delete($content->banner_image);
            }
            $data['banner_image'] = $request->file('banner_image')->store('about', 'public');
        }

        // Story Image
        if ($request->hasFile('story_image')) {
            // Pehle purani story image delete karein agar exist karti hai
            if (isset($content->story['image'])) {
                Storage::disk('public')->delete($content->story['image']);
            }
            $data['story']['image'] = $request->file('story_image')->store('about', 'public');
        }

        $content->update($data);
        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'message' => 'About page updated successfully.',
            'data' => $content->fresh(),
        ]);
    }
}
