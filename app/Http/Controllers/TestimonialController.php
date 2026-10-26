<?php

namespace App\Http\Controllers;

use App\Http\Requests\TestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TestimonialController extends Controller
{
    private const CACHE_KEY = 'approved_testimonials';

    // Homepage: approved testimonials only
    public function index(): JsonResponse
    {
        $testimonials = Cache::remember(
            self::CACHE_KEY,
            3600,
            fn () => Testimonial::with('user:id,name')
                ->where('is_verified', true)
                ->latest()
                ->paginate(10)
        );

        return response()->json([
            'status' => 'success',
            'data' => $testimonials,
        ]);
    }

    // Logged-in user submits a testimonial
    public function store(TestimonialRequest $request): JsonResponse
    {
        $testimonial = Testimonial::create([
            'user_id' => $request->user()->id,
            'ratings' => $request->validated('ratings'),
            'reviews' => $request->validated('reviews'),
            'is_verified' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Your review has been submitted for approval.',
            'data' => $testimonial,
        ], 201);
    }

    // Admin: all testimonials, including pending ones
    public function adminIndex(): JsonResponse
    {
        $testimonials = Testimonial::with('user:id,name,email')
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $testimonials,
        ]);
    }

    // Admin: approve or unapprove a testimonial
    public function toggleVerification(
        Request $request,
        Testimonial $testimonial
    ): JsonResponse {
        $validated = $request->validate([
            'is_verified' => ['required', 'boolean'],
        ]);

        $wasVerified = $testimonial->is_verified;
        $shouldVerify = (bool) $validated['is_verified'];

        if ($wasVerified === $shouldVerify) {
            return response()->json([
                'status' => 'success',
                'message' => 'No changes were needed.',
                'data' => $testimonial,
            ]);
        }

        DB::transaction(function () use ($testimonial, $shouldVerify) {
            $testimonial->update([
                'is_verified' => $shouldVerify,
            ]);
        });

        Cache::forget(self::CACHE_KEY);

        // Send email only when a pending review is approved
        if (! $wasVerified && $shouldVerify) {
            $testimonial->loadMissing('user');

            try {
                Mail::raw(
                    "Hello {$testimonial->user->name},\n\n"
                    . "Your testimonial has been approved and can now appear "
                    . "on our website.\n\n"
                    . "Thank you for sharing your feedback.",
                    function ($message) use ($testimonial) {
                        $message
                            ->to($testimonial->user->email)
                            ->subject('Your testimonial has been approved');
                    }
                );
            } catch (\Throwable $exception) {
                Log::error('Testimonial approval email failed.', [
                    'testimonial_id' => $testimonial->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => $shouldVerify
                ? 'Testimonial approved successfully.'
                : 'Testimonial approval revoked.',
            'data' => $testimonial->fresh()->load('user:id,name,email'),
        ]);
    }
}
