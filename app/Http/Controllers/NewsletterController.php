<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterRequest;
use App\Models\Newsletter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class NewsletterController extends Controller
{
    private const CACHE_KEY = 'newsletter_list';

    // Public: subscribe with email
    public function store(NewsletterRequest $request): JsonResponse
    {
        $newsletter = Newsletter::create($request->validated());

        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully subscribed to the newsletter.',
            'data' => [
                'id' => $newsletter->id,
                'email' => $newsletter->email,
            ],
        ], 201);
    }

    // Admin: view all subscribers
    public function index(): JsonResponse
    {
        $newsletters = Cache::remember(
            self::CACHE_KEY,
            3600,
            fn () => Newsletter::latest()->paginate(20)
        );

        return response()->json([
            'status' => 'success',
            'data' => $newsletters,
        ]);
    }

    // Admin: delete subscriber
    public function destroy(Newsletter $newsletter): JsonResponse
    {
        $newsletter->delete();

        Cache::forget(self::CACHE_KEY);

        return response()->json([
            'status' => 'success',
            'message' => 'Newsletter subscriber deleted successfully.',
        ]);
    }
}

