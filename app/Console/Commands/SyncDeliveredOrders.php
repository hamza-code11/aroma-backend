<?php

namespace App\Console\Commands;

use App\Models\Checkout;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncDeliveredOrders extends Command
{
    protected $signature = 'orders:sync-delivered';
    protected $description = 'Check FedEx tracking for shipped orders and update status to delivered';

    public function handle()
    {
        // Un orders ko dhoondein jo 'shipped' hain aur tracking number mojood hai
        $orders = Checkout::where('status', 'shipped')
            ->whereNotNull('tracking_number')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No shipped orders to check.');
            return;
        }

        // FedEx Token (aapka pehle se bana hua method use karein)
        $token = $this->getFedExTrackToken();
        if (!$token) {
            Log::error('SyncDeliveredOrders: FedEx token failed.');
            return;
        }

        $baseUrl = rtrim(env('FEDEX_BASE_URL', 'https://apis-sandbox.fedex.com'), '/');

        foreach ($orders as $order) {
            try {
                $response = Http::withToken($token)
                    ->timeout(30)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'X-locale' => 'en_US',
                    ])
                    ->post("{$baseUrl}/track/v1/trackingnumbers", [
                        'includeDetailedScans' => true,
                        'trackingInfo' => [
                            ['trackingNumberInfo' => ['trackingNumber' => $order->tracking_number]]
                        ],
                    ]);

                $data = $response->json();
                $trackResult = $data['output']['completeTrackResults'][0]['trackResults'][0] ?? null;

                if ($trackResult && isset($trackResult['latestStatusDetail']['code'])) {
                    $statusCode = $trackResult['latestStatusDetail']['code'];
                    
                    // FedEx mein 'DL' ka matlab Delivered hota hai
                    if ($statusCode === 'DL') {
                        $order->update(['status' => 'delivered']);
                        Log::info("Order #{$order->id} marked as delivered via FedEx sync.");
                    }
                }
            } catch (\Exception $e) {
                Log::error("SyncDeliveredOrders failed for Order #{$order->id}: " . $e->getMessage());
            }
        }

        $this->info('Order sync completed.');
    }

    // Aapka FedEx Track Token method (copy paste from FedExController)
    private function getFedExTrackToken()
    {
        $baseUrl = rtrim(env('FEDEX_BASE_URL', 'https://apis-sandbox.fedex.com'), '/');
        $response = Http::asForm()->post("{$baseUrl}/oauth/token", [
            'grant_type' => 'client_credentials',
            'client_id' => env('FEDEX_TRACK_CLIENT_ID'),
            'client_secret' => env('FEDEX_TRACK_CLIENT_SECRET'),
        ]);
        return $response->successful() ? $response->json()['access_token'] : null;
    }
}