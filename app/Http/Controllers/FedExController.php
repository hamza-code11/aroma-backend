<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FedExController extends Controller
{
    /**
     * Get Shipping Rates from FedEx
     */
    public function getRates(Request $request)
    {
        // 1. Validation
        $validated = $request->validate([
            'addressData.address' => 'required|string',
            'addressData.city' => 'required|string',
            'addressData.postal_code' => 'required|string',
            'addressData.country' => 'required|string',
            'cartItems' => 'required|array|min:1',
        ]);

        $destination = $validated['addressData'];
        $cartItems = $validated['cartItems'];

        // 2. Get FedEx Token
        $token = $this->getFedExToken();
        if (!$token) {
            Log::error('FedEx: Token generation failed');
            return $this->getMockRates('Authentication failed. Using fallback rates.');
        }

        // 3. Get Origin Data
        $originPath = storage_path('app/fedex/origin.json');
        if (!file_exists($originPath)) {
            return $this->getMockRates('Origin config missing. Using fallback rates.');
        }
        $origin = json_decode(file_get_contents($originPath), true);

        // 4. Get Country Code
        $countryCode = $this->getCountryCode($destination['country']);

        // 5. Build Packages
        $packages = [];
        foreach ($cartItems as $item) {
            $packages[] = [
                "weight" => [
                    "units" => "KG",
                    "value" => max(0.1, (float)($item['weight'] ?? 0.5) * (int)($item['quantity'] ?? 1))
                ],
                "dimensions" => [
                    "length" => (int)($item['length'] ?? 10),
                    "width"  => (int)($item['width'] ?? 10),
                    "height" => (int)($item['height'] ?? 10),
                    "units"  => "CM"
                ]
            ];
        }

        // 6. Build Payload
        $payload = [
            "accountNumber" => [
                "value" => env('FEDEX_ACCOUNT_NUMBER')
            ],
            "requestedShipment" => [
                "shipper" => [
                    "address" => [
                        "streetLines" => [$origin['street'] ?? ''],
                        "city" => $origin['city'] ?? '',
                        "stateOrProvinceCode" => $origin['state'] ?? '',
                        "postalCode" => $origin['postal_code'] ?? '',
                        "countryCode" => $origin['country'] ?? 'US'
                    ]
                ],
                "recipient" => [
                    "address" => [
                        "streetLines" => [$destination['address']],
                        "city" => $destination['city'],
                        "stateOrProvinceCode" => $destination['state'] ?? 'CA',
                        "postalCode" => $destination['postal_code'],
                        "countryCode" => $countryCode
                    ]
                ],
                "pickupType" => "DROPOFF_AT_FEDEX_LOCATION",
                "rateRequestType" => ["ACCOUNT", "LIST"],
                "requestedPackageLineItems" => $packages
            ]
        ];

        try {
            $baseUrl = rtrim(env('FEDEX_BASE_URL', 'https://apis-sandbox.fedex.com'), '/');
            // 7. Call FedEx API
            $response = Http::withToken($token)
                ->timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("{$baseUrl}/rate/v1/rates/quotes", $payload);

            $data = $response->json();

            // 8. Transform Rates
            if ($response->successful() && isset($data['output']['rateReplyDetails'])) {
                $rates = $this->transformRates($data['output']['rateReplyDetails']);
                if (!empty($rates)) {
                    return response()->json([
                        'success' => true,
                        'rates' => $rates,
                    ]);
                }
            }

            // 9. Fallback to Mock Rates
            Log::warning('FedEx API Error:', ['response' => $data]);
            return $this->getMockRates('FedEx API error. Using fallback rates.');

        } catch (\Exception $e) {
            Log::error('FedEx Exception: ' . $e->getMessage());
            return $this->getMockRates('Connection error. Using fallback rates.');
        }
    }

    /**
     * Transform FedEx Response
     */
    private function transformRates($rateDetails)
    {
        $rates = [];
        foreach ($rateDetails as $rate) {
            $serviceType = $rate['serviceType'] ?? 'UNKNOWN';
            $serviceName = $rate['serviceName'] ?? $serviceType;
            
            $amount = 0;
            if (isset($rate['ratedShipmentDetails'][0]['totalNetCharge'])) {
                $amount = (float)$rate['ratedShipmentDetails'][0]['totalNetCharge'];
            }

            if ($amount > 0) {
                $rates[] = [
                    'id' => $serviceType,
                    'name' => $serviceName,
                    'price' => $amount,
                    'days' => $this->extractDeliveryDays($rate),
                ];
            }
        }
        return $rates;
    }

    /**
     * Extract Delivery Days
     */
    private function extractDeliveryDays($rate)
    {
        if (isset($rate['operationalDetail']['astraDescription'])) {
            return $rate['operationalDetail']['astraDescription'];
        }
        
        $serviceType = $rate['serviceType'] ?? '';
        return match (true) {
            str_contains($serviceType, 'OVERNIGHT') => '1 business day',
            str_contains($serviceType, '2_DAY') => '2 business days',
            str_contains($serviceType, 'EXPRESS') => '2-3 business days',
            default => '3-5 business days'
        };
    }

    /**
     * Country Code Helper
     */
    private function getCountryCode($country)
    {
        return match (strtolower($country)) {
            'pakistan' => 'PK',
            'united states', 'usa' => 'US',
            'united kingdom', 'uk' => 'GB',
            'uae', 'dubai' => 'AE',
            'saudi arabia' => 'SA',
            default => 'US'
        };
    }

    /**
     * Get FedEx OAuth Token
     */
    private function getFedExToken()
    {
        try {
            $clientId = env('FEDEX_CLIENT_ID');
            $clientSecret = env('FEDEX_CLIENT_SECRET');

            if (empty($clientId) || empty($clientSecret)) {
                return null;
            }

            $baseUrl = rtrim(env('FEDEX_BASE_URL', 'https://apis-sandbox.fedex.com'), '/');
            $response = Http::asForm()
                ->timeout(15)
                ->post("{$baseUrl}/oauth/token", [
                    'grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret
                ]);

            return $response->successful() ? $response->json()['access_token'] : null;

        } catch (\Exception $e) {
            Log::error('FedEx Token Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fallback Mock Rates (agar FedEx fail ho jaye)
     */
    private function getMockRates($reason = 'Service unavailable')
    {
        Log::info('FedEx: Returning mock rates - ' . $reason);

        return response()->json([
            'success' => true,
            'mock' => true,
            'rates' => [
                [
                    'id' => 'FEDEX_GROUND',
                    'name' => 'FedEx Ground',
                    'price' => 12.50,
                    'days' => '3-5 business days'
                ],
                [
                    'id' => 'FEDEX_2_DAY',
                    'name' => 'FedEx 2Day',
                    'price' => 22.00,
                    'days' => '2 business days'
                ],
                [
                    'id' => 'FEDEX_PRIORITY_OVERNIGHT',
                    'name' => 'FedEx Priority Overnight',
                    'price' => 45.00,
                    'days' => '1 business day'
                ],
            ]
        ]);
    }



    
        /**
     * Create FedEx Shipment for an Order (Production Ready)
     */
    public function createShipment($id)
    {
        try {
            // 1. Load Order
            $checkout = \App\Models\Checkout::with('items')->findOrFail($id);

            // 2. Check if already shipped
            if ($checkout->tracking_number) {
                return response()->json([
                    'success'         => true,
                    'message'         => 'Shipment already created',
                    'tracking_number' => $checkout->tracking_number,
                    'label_url'       => $checkout->label_url,
                ]);
            }

            // 3. Get FedEx Token
            $token = $this->getFedExToken();
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'FedEx authentication failed. Check API credentials.',
                ], 500);
            }

            // 4. Load Origin (Warehouse) Address
            $originPath = storage_path('app/fedex/origin.json');
            $origin = file_exists($originPath) ? json_decode(file_get_contents($originPath), true) : null;

            if (!$origin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Origin warehouse address not configured.',
                ], 500);
            }

            // 5. Determine Country Code
            $countryRaw = strtolower(trim($checkout->country ?? 'us'));
            $countryCode = match (true) {
                in_array($countryRaw, ['pakistan', 'pk'])                    => 'PK',
                in_array($countryRaw, ['united states', 'usa', 'us'])        => 'US',
                in_array($countryRaw, ['united kingdom', 'uk', 'gb'])        => 'GB',
                in_array($countryRaw, ['canada', 'ca'])                      => 'CA',
                in_array($countryRaw, ['australia', 'au'])                   => 'AU',
                in_array($countryRaw, ['uae', 'dubai', 'ae'])                => 'AE',
                in_array($countryRaw, ['india', 'in'])                       => 'IN',
                default                                                       => 'US',
            };

            $isInternational = ($countryCode !== 'US');
            $serviceType     = $isInternational ? 'INTERNATIONAL_PRIORITY' : 'FEDEX_GROUND';

            // 6. Sanitize Phone
            $phone = preg_replace('/[^0-9]/', '', $checkout->phone ?? '5555555555');
            $phone = substr($phone, 0, 15) ?: '5555555555';

            // 7. Build Packages
            $packages = [];
            foreach ($checkout->items as $item) {
                $packages[] = [
                    'weight' => [
                        'units' => 'LB',
                        'value' => max(0.5, (float)($item->weight ?? 1)),
                    ],
                    'dimensions' => [
                        'length' => 10,
                        'width'  => 8,
                        'height' => 4,
                        'units'  => 'IN',
                    ],
                    'itemDescription' => substr($item->product_name ?? 'Product', 0, 50),
                    'declaredValue'   => [
                        'amount'   => number_format((float)($item->price ?? 1.00), 2, '.', ''),
                        'currency' => 'USD',
                    ],
                ];
            }

            // 8. Account Number
            $accountNumber = env('FEDEX_ACCOUNT_NUMBER');

            // 9. Base Payload
            $payload = [
                'labelResponseOptions' => 'URL_ONLY',
                'accountNumber'        => ['value' => $accountNumber],
                'requestedShipment'    => [
                    'shipper' => [
                        'contact' => [
                            'personName'  => substr($origin['name'] ?? 'iShine Aroma', 0, 35),
                            'phoneNumber' => preg_replace('/[^0-9]/', '', $origin['phone'] ?? '5555555555'),
                        ],
                        'address' => [
                            'streetLines'         => [substr($origin['street'] ?? '123 Main St', 0, 35)],
                            'city'                => $origin['city'] ?? 'Harrison',
                            'stateOrProvinceCode' => $origin['state'] ?? 'AR',
                            'postalCode'          => $origin['postal_code'] ?? '72601',
                            'countryCode'         => $origin['country'] ?? 'US',
                        ],
                    ],
                    'recipients' => [[
                        'contact' => [
                            'personName'  => substr($checkout->full_name ?? 'Customer', 0, 35),
                            'phoneNumber' => $phone,
                        ],
                        'address' => [
                            'streetLines'         => [substr($checkout->address ?? '7372 Intermodal Dr', 0, 35)],
                            'city'                => $checkout->city ?? 'Louisville',
                            'stateOrProvinceCode' => $isInternational ? '' : 'CA',
                            'postalCode'          => $checkout->postal_code ?? '40209',
                            'countryCode'         => $countryCode,
                            'residential'         => false,
                        ],
                    ]],
                    'shipDatestamp' => now()->addDay()->format('Y-m-d'),
                    'serviceType'   => $serviceType,
                    'packagingType' => 'YOUR_PACKAGING',
                    'pickupType'    => 'DROPOFF_AT_FEDEX_LOCATION',
                    'shippingChargesPayment' => [
                        'paymentType' => 'SENDER',
                        'payor'       => [
                            'responsibleParty' => [
                                'accountNumber' => ['value' => $accountNumber],
                                'address'       => ['countryCode' => 'US'],
                            ],
                        ],
                    ],
                    'labelSpecification' => [
                        'imageType'      => 'PDF',
                        'labelStockType' => 'PAPER_4X6',
                    ],
                    'requestedPackageLineItems' => $packages,
                ],
            ];

            // 10. International Shipment ke liye Customs add karein
            if ($isInternational) {
                $commodities = [];
                $totalValue  = 0;

                foreach ($checkout->items as $item) {
                    $itemValue   = (float)($item->price ?? 1.00) * (int)($item->quantity ?? 1);
                    $totalValue += $itemValue;

                    $commodities[] = [
                        'description'          => substr($item->product_name ?? 'Product', 0, 50),
                        'quantity'             => (int)($item->quantity ?? 1),
                        'quantityUnits'        => 'PCS',
                        'weight'               => [
                            'units' => 'LB',
                            'value' => max(0.5, (float)($item->weight ?? 1)),
                        ],
                        'unitPrice'            => [
                            'amount'   => number_format((float)($item->price ?? 1.00), 2, '.', ''),
                            'currency' => 'USD',
                        ],
                        'customsValue'         => [
                            'amount'   => number_format($itemValue, 2, '.', ''),
                            'currency' => 'USD',
                        ],
                        'countryOfManufacture' => 'US',
                        'harmonizedCode'       => '3307410000', // Aroma/Diffuser ka HS Code
                        'numberOfPieces'       => (int)($item->quantity ?? 1),
                    ];
                }

                $payload['requestedShipment']['customsClearanceDetail'] = [
                    'dutiesPayment' => [
                        'paymentType' => 'SENDER',
                        'payor'       => [
                            'responsibleParty' => [
                                'accountNumber' => ['value' => $accountNumber],
                                'address'       => ['countryCode' => 'US'],
                            ],
                        ],
                    ],
                    'commodities'        => $commodities,
                    'totalCustomsValue'  => [
                        'amount'   => number_format($totalValue, 2, '.', ''),
                        'currency' => 'USD',
                    ],
                ];
            }

            // 11. Call FedEx API
            $baseUrl = rtrim(env('FEDEX_BASE_URL', 'https://apis-sandbox.fedex.com'), '/');
            
            \Log::info('FedEx Shipment Request:', [
                'checkout_id' => $checkout->id,
                'payload' => $payload
            ]);

            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ])
                ->post("{$baseUrl}/ship/v1/shipments", $payload);

            $data = $response->json();

            // 12. Success Response
            if ($response->successful() && isset($data['output']['transactionShipments'][0])) {
                $shipment       = $data['output']['transactionShipments'][0];
                $trackingNumber = $shipment['pieceResponses'][0]['trackingNumber'] ?? 'TRK-' . uniqid();
                
                // FedEx sometimes returns labels in different places
                $labelUrl = $shipment['pieceResponses'][0]['packageDocuments'][0]['url']
                         ?? $shipment['shipmentDocuments'][0]['url']
                         ?? null;

                $checkout->update([
                    'tracking_number' => $trackingNumber,
                    'status'          => 'shipped',
                    'label_url'       => $labelUrl,
                    'shipped_at'      => now(),
                ]);

                \Log::info('FedEx Shipment Created', [
                    'checkout_id' => $checkout->id,
                    'tracking'    => $trackingNumber,
                    'label_url'   => $labelUrl,
                ]);

                return response()->json([
                    'success'         => true,
                    'message'         => 'Shipment created successfully',
                    'tracking_number' => $trackingNumber,
                    'label_url'       => $labelUrl,
                ]);
            }

            // 13. Error Handling
            $errorMsg = $data['errors'][0]['message'] ?? 'FedEx API error';
            
            \Log::error('FedEx Shipment Failed:', [
                'response' => $data,
                'payload'  => $payload
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMsg,
            ], 500);

        } catch (\Exception $e) {
            \Log::error('Shipment Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }








    /**
     * Tracking project (Basic Integrated Visibility) ka token.
     * Ship wale token se alag cache hota hai.
     */
    private function getFedExTrackToken(bool $forceRefresh = false): ?string
    {
        $baseUrl  = rtrim(env('FEDEX_BASE_URL', 'https://apis-sandbox.fedex.com'), '/');
        $cacheKey = 'fedex_track_token_' . md5($baseUrl . env('FEDEX_TRACK_CLIENT_ID'));

        if ($forceRefresh) {
            \Cache::forget($cacheKey);
        }

        return \Cache::remember($cacheKey, 3300, function () use ($baseUrl) {
            try {
                $response = Http::asForm()
                    ->timeout(30)
                    ->post("{$baseUrl}/oauth/token", [
                        'grant_type'    => 'client_credentials',
                        'client_id'     => env('FEDEX_TRACK_CLIENT_ID'),
                        'client_secret' => env('FEDEX_TRACK_CLIENT_SECRET'),
                    ]);

                if ($response->successful() && $response->json('access_token')) {
                    return $response->json('access_token');
                }

                \Log::error('FedEx Track token error: ' . $response->body());
                return null;
            } catch (\Exception $e) {
                \Log::error('FedEx Track token exception: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Track a shipment using FedEx Basic Integrated Visibility API
     */
    public function trackShipment(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string|max:50',
        ]);

        $trackingNumber = trim($request->tracking_number);
        $baseUrl = rtrim(env('FEDEX_BASE_URL', 'https://apis-sandbox.fedex.com'), '/');

        $token = $this->getFedExTrackToken();
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'FedEx tracking authentication failed.',
            ], 500);
        }

        $payload = [
            'includeDetailedScans' => true,
            'trackingInfo' => [
                [
                    'trackingNumberInfo' => [
                        'trackingNumber' => $trackingNumber,
                    ],
                ],
            ],
        ];

        try {
            $send = function (string $token) use ($baseUrl, $payload) {
                return Http::withToken($token)
                    ->timeout(30)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept'       => 'application/json',
                        'X-locale'     => 'en_US',
                    ])
                    ->post("{$baseUrl}/track/v1/trackingnumbers", $payload);
            };

            $response = $send($token);

            // Token expire ho gaya ho to naya token lekar ek baar retry
            if ($response->status() === 401) {
                $token = $this->getFedExTrackToken(true);
                if (!$token) {
                    return response()->json([
                        'success' => false,
                        'message' => 'FedEx tracking authentication failed.',
                    ], 500);
                }
                $response = $send($token);
            }

            $data = $response->json();

            if (!$response->successful()) {
                \Log::warning('FedEx Track failed: ' . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => $data['errors'][0]['message'] ?? 'Tracking request failed.',
                ], $response->status() >= 500 ? 502 : 422);
            }

            $trackResult = $data['output']['completeTrackResults'][0]['trackResults'][0] ?? null;

            if (!$trackResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tracking information not found.',
                ], 404);
            }

            // FedEx kabhi 200 ke saath bhi error deta hai (jaise tracking number not found)
            if (!empty($trackResult['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $trackResult['error']['message'] ?? 'Tracking number not found.',
                    'code'    => $trackResult['error']['code'] ?? null,
                ], 404);
            }

            $latestStatus = $trackResult['latestStatusDetail']['description'] ?? 'In Transit';
            $statusCode   = $trackResult['latestStatusDetail']['code'] ?? '';

            // Scan events history
            $events = [];
            foreach (($trackResult['scanEvents'] ?? []) as $scan) {
                $loc = $scan['scanLocation'] ?? [];
                $events[] = [
                    'date'        => $scan['date'] ?? null,
                    'description' => $scan['eventDescription'] ?? null,
                    'location'    => trim(
                        implode(', ', array_filter([
                            $loc['city'] ?? null,
                            $loc['stateOrProvinceCode'] ?? null,
                            $loc['countryName'] ?? ($loc['countryCode'] ?? null),
                        ])),
                        ', '
                    ),
                ];
            }

            // Estimated delivery: pehle time window, warna dateAndTimes se
            $estimatedDelivery = $trackResult['estimatedDeliveryTimeWindow']['window']['ends'] ?? null;

            if (!$estimatedDelivery) {
                foreach (($trackResult['dateAndTimes'] ?? []) as $dt) {
                    if (($dt['type'] ?? '') === 'ESTIMATED_DELIVERY') {
                        $estimatedDelivery = $dt['dateTime'] ?? null;
                        break;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tracking_number'    => $trackResult['trackingNumberInfo']['trackingNumber'] ?? $trackingNumber,
                    'status'             => $latestStatus,
                    'status_code'        => $statusCode,
                    'service'            => $trackResult['serviceDetail']['description'] ?? null,
                    'estimated_delivery' => $estimatedDelivery,
                    'events'             => $events,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('FedEx Tracking Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to track shipment.',
            ], 500);
        }
    }


}
