<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactCmsRequest;
use App\Models\ContactCms;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ContactCmsController extends Controller
{
    private const CACHE_KEY = 'contact_cms_content';

    // Public: Get contact page content
    public function show(): JsonResponse
    {
        $content = Cache::remember(self::CACHE_KEY, 3600, function () {
            return ContactCms::first(); // Assuming sirf 1 record hai
        });

        return response()->json([
            'status' => 'success',
            'data' => $content,
        ]);
    }

    // Admin: Update content
    public function update(ContactCmsRequest $request): JsonResponse
    {
        $content = ContactCms::firstOrCreate([], [
            'hero' => $request->hero,
            'banner_image' => null,
            'info' => $request->info,
            'faq' => $request->faq,
        ]);

        $data = $request->validated();

        // Image upload handling
        if ($request->hasFile('banner_image')) {
            if ($content->banner_image) {
                Storage::disk('public')->delete($content->banner_image);
            }
            $data['banner_image'] = $request->file('banner_image')->store('contact', 'public');
        } else {
            // Agar nayi image nahi aayi, toh purani rakho
            unset($data['banner_image']);
        }

        $content->update($data);

        // Cache clear
        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'message' => 'Contact page updated successfully.',
            'data' => $content->fresh(),
        ]);
    }
}