<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaxIdRequest;
use App\Models\TaxId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaxIdSubmittedMail;
use App\Mail\TaxIdStatusUpdatedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class TaxIdController extends Controller
{
    private const CACHE_KEY = 'tax_ids_list';

    public function store(TaxIdRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $taxId = TaxId::updateOrCreate(
            ['user_id' => $user->id],
            [
                'tax_id' => $request->tax_id,
                'status' => 'pending',
            ]
        );

        Cache::forget(self::CACHE_KEY);

        // === YAHAN HAI ASAL FIX ===
        // Response bhejne ke baad email send karein (background mein)
        App::terminating(function () use ($user, $taxId) {
            try {
                Mail::to($user->email)->send(new TaxIdSubmittedMail($taxId->load('user')));
            } catch (\Throwable $e) {
                Log::error('Tax ID submission email failed: ' . $e->getMessage());
            }
        });

        // User ko foran response
        return response()->json([
            'status' => 'success',
            'message' => 'Tax ID submitted for verification.',
            'data' => $taxId,
        ], 201);
    }

    public function myTaxId(Request $request): JsonResponse
    {
        $taxId = TaxId::where('user_id', $request->user()->id)->first();

        return response()->json([
            'status' => 'success',
            'data' => $taxId,
        ]);
    }

    public function index(): JsonResponse
    {
        $taxIds = Cache::remember(self::CACHE_KEY, 3600, function () {
            return TaxId::with('user:id,name,email')
                ->latest()
                ->paginate(20);
        });

        return response()->json([
            'status' => 'success',
            'data' => $taxIds,
        ]);
    }

    
    public function updateStatus(Request $request, TaxId $taxId): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,verified,rejected'],
        ]);

        $oldStatus = $taxId->status;
        $taxId->update(['status' => $validated['status']]);
        $taxId->load('user:id,name,email');

        Cache::forget(self::CACHE_KEY);

        // Email send karne ke liye afterResponse
        if ($oldStatus !== $validated['status']) {
            App::terminating(function () use ($taxId, $validated) {
                try {
                    Mail::to($taxId->user->email)->send(
                        new TaxIdStatusUpdatedMail($taxId, $validated['status'])
                    );
                } catch (\Throwable $e) {
                    Log::error('Tax ID status email failed: ' . $e->getMessage());
                }
            });
        }

        return response()->json([
            'status' => 'success',
            'message' => "Tax ID status updated to {$validated['status']}.",
            'data' => $taxId,
        ]);
    }
}