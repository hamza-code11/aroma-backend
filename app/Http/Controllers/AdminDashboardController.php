<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Checkout;
use App\Models\User;
use App\Models\TaxId;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AdminDashboardController extends Controller
{
    // Cache key constant (taake sab jagah same use ho)
    private const DASHBOARD_CACHE_KEY = 'admin_dashboard_stats';

    public function getStats(): JsonResponse
    {
        // 1 ghante (3600 seconds) ke liye cache karein
        $data = Cache::remember(self::DASHBOARD_CACHE_KEY, 3600, function () {
            
            $totalProducts = Product::count();
            $totalOrders = Checkout::where('status', '!=', 'draft')->count();
            $totalCustomers = User::where('role', 'user')->count();
            
            // Revenue: Sirf 'paid' orders ka total
            $totalRevenue = Checkout::where('payment_status', 'paid')->sum('total');
            
            // Tax ID Stats
            $pendingTaxIds = TaxId::where('status', 'pending')->count();
            $recentTaxIds = TaxId::with('user:id,name,email')->where('status', 'pending')->latest()->take(3)->get();

            // Recent 5 Orders
            $recentOrders = Checkout::with(['user:id,name,email', 'items'])
                ->where('status', '!=', 'draft')
                ->latest()
                ->take(5)
                ->get();

            return [
                'stats' => [
                    'products' => $totalProducts,
                    'orders' => $totalOrders,
                    'customers' => $totalCustomers,
                    'revenue' => $totalRevenue,
                ],
                'pending_tax_ids_count' => $pendingTaxIds,
                'recent_tax_ids' => $recentTaxIds,
                'recent_orders' => $recentOrders,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
}