<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireVerifiedTaxId
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $taxId = $user->taxIds()->latest()->first();

        if (!$taxId || $taxId->status !== 'verified') {
            return response()->json([
                'status' => 'error',
                'message' => 'Your Tax ID must be verified before you can checkout.',
            ], 403);
        }

        return $next($request);
    }
}
