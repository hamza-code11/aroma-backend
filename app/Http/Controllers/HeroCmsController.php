<?php

namespace App\Http\Controllers;

use App\Http\Requests\HeroCmsRequest;
use App\Models\HeroCms;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class HeroCmsController extends Controller
{
    private const CACHE_KEY = 'hero_cms_content';

    // Public: Get hero content
    public function show(): JsonResponse
    {
        $content = Cache::remember(self::CACHE_KEY, 3600, function () {
            return HeroCms::first();
        });

        return response()->json([
            'status' => 'success',
            'data' => $content,
        ]);
    }

    // Admin: Update hero content
    public function update(HeroCmsRequest $request): JsonResponse
    {
        $content = HeroCms::firstOrCreate([], [
            'label' => $request->label,
            'title' => $request->title,
            'description' => $request->description,
            'primary_btn_label' => $request->primary_btn_label,
            'primary_btn_link' => $request->primary_btn_link,
            'secondary_btn_label' => $request->secondary_btn_label,
            'secondary_btn_link' => $request->secondary_btn_link,
        ]);

        $content->update($request->validated());
        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'message' => 'Hero content updated successfully.',
            'data' => $content->fresh(),
        ]);
    }
}
