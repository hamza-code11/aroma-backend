<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\CheckoutItem;
use App\Models\TaxId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Process Payment & Create Order
     */
    public function processPayment(Request $request)
    {
        // 1. Validation
        $request->validate([
            'checkout_id'      => 'required|exists:checkouts,id',
            'card_number'      => 'required|string|min:13|max:19',
            'expiry_month'     => 'required|string|size:2',
            'expiry_year'      => 'required|string|size:4',
            'cvv'              => 'required|string|min:3|max:4',
            'amount'           => 'required|numeric|min:0.01',
            'shipping_method'  => 'required|string',
            'shipping_price'   => 'required|numeric',
            'items'            => 'required|array|min:1',
            'items.*.product_id'   => 'nullable',
            'items.*.name'         => 'required|string',
            'items.*.price'        => 'required|numeric',
            'items.*.quantity'     => 'required|integer|min:1',
        ]);

        $userId = auth()->id();

        // 2. Verify Tax ID
        $taxApproved = TaxId::where('user_id', $userId)
            ->where('status', 'verified')
            ->exists();

        if (!$taxApproved) {
            return response()->json([
                'success' => false,
                'message' => 'Tax ID verification required before payment.',
            ], 403);
        }

        // 3. Verify Checkout (Draft)
        $checkout = Checkout::where('id', $request->checkout_id)
            ->where('user_id', $userId)
            ->where('status', 'draft')
            ->first();

        if (!$checkout) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or already completed checkout.',
            ], 400);
        }

        // 4. Charge Payment
        $paymentResult = $this->chargePayment(
            $request->card_number,
            $request->expiry_month,
            $request->expiry_year,
            $request->cvv,
            $request->amount,
            $checkout
        );

        if (!$paymentResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $paymentResult['message'],
                'error_code' => $paymentResult['error_code'] ?? null,
            ], 400);
        }

        // 5. Create Order
        DB::beginTransaction();
        try {
            $subtotal = collect($request->items)->sum(fn($item) => $item['price'] * $item['quantity']);
            $total = $subtotal + $request->shipping_price;

            $checkout->update([
                'shipping_method' => $request->shipping_method,
                'shipping_price'  => $request->shipping_price,
                'subtotal'        => $subtotal,
                'total'           => $total,
                'payment_method'  => 'card',
                'payment_status'  => 'paid',
                'status'          => 'completed',
                'paid_at'         => now(),
                'transaction_id'  => $paymentResult['transaction_id'],
            ]);

            $checkout->items()->delete();

            $items = array_map(fn($item) => [
                'checkout_id'    => $checkout->id,
                'product_id'     => $item['product_id'] ?? null,
                'product_name'   => $item['name'],
                'price'          => $item['price'],
                'quantity'       => $item['quantity'],
                'subtotal'       => $item['price'] * $item['quantity'],
                'created_at'     => now(),
                'updated_at'     => now(),
            ], $request->items);

            CheckoutItem::insert($items);

            DB::commit();

            Log::info('Payment successful', [
                'user_id'        => $userId,
                'checkout_id'    => $checkout->id,
                'transaction_id' => $paymentResult['transaction_id'],
                'amount'         => $total,
            ]);

            // TODO: Aap yahan FedEx Shipment create karne ka Job/Function call kar sakte hain
            // (jaise aapke purane project mein `CreateFedExShipmentJob` tha)

            return response()->json([
                'success'        => true,
                'message'        => 'Payment successful! Order placed.',
                'order_id'       => $checkout->id,
                'transaction_id' => $paymentResult['transaction_id'],
                'total'          => $total,
                'redirect_url'   => '/order-confirmation/' . $checkout->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Payment successful but order creation failed. Contact support.',
            ], 500);
        }
    }

    /**
     * Charge Payment using Authorize.net
     */
    private function chargePayment($cardNumber, $expiryMonth, $expiryYear, $cvv, $amount, $checkout)
    {
        try {
            // Sanitize card number
            $cardNumber = str_replace(' ', '', $cardNumber);

            $loginId        = config('authorize.api_login_id');
            $transactionKey = config('authorize.transaction_key');
            $environment    = config('authorize.environment');

            $isProduction = $environment === 'production';
            $apiUrl = $isProduction
                ? 'https://api.authorize.net/xml/v1/request.api'
                : 'https://apitest.authorize.net/xml/v1/request.api';

            // Split full name into first/last
            $nameParts = explode(' ', $checkout->full_name, 2);
            $firstName = $nameParts[0] ?? 'Customer';
            $lastName  = $nameParts[1] ?? 'User';

            // Payload
            $payload = [
                "createTransactionRequest" => [
                    "merchantAuthentication" => [
                        "name"           => $loginId,
                        "transactionKey" => $transactionKey,
                    ],
                    "refId" => "ref" . time(),
                    "transactionRequest" => [
                        "transactionType" => "authCaptureTransaction",
                        "amount"          => number_format((float)$amount, 2, '.', ''),
                        "payment" => [
                            "creditCard" => [
                                "cardNumber"     => $cardNumber,
                                "expirationDate" => $expiryYear . '-' . $expiryMonth,
                                "cardCode"       => $cvv,
                            ]
                        ],
                        "order" => [
                            "invoiceNumber" => "INV-" . $checkout->id . "-" . time(),
                            "description"   => "Order #" . $checkout->id,
                        ],
                        "billTo" => [
                            "firstName" => $firstName,
                            "lastName"  => $lastName,
                            "address"   => $checkout->address,
                            "city"      => $checkout->city,
                            "zip"       => $checkout->postal_code,
                            "country"   => $this->getCountryName($checkout->country),
                        ],
                    ],
                ]
            ];

            // API Call
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ])
                ->post($apiUrl, $payload);

            $rawBody = $response->body();

            // Parse JSON (BOM removal)
            $jsonString = preg_replace('/^[\x00-\x1F\x80-\xFF]*/', '', $rawBody);
            $fullData = json_decode($jsonString, true);

            if (!$fullData) {
                Log::error('Authorize.net parsing failed', ['raw_body' => $rawBody]);
                return ['success' => false, 'message' => 'Gateway communication error.'];
            }

            $createResponse = $fullData['createTransactionResponse'] ?? $fullData;

            if (!$createResponse) {
                Log::error('Authorize.net: createTransactionResponse missing', ['full' => $fullData]);
                return ['success' => false, 'message' => 'Invalid gateway response.'];
            }

            Log::info('Authorize.net Parsed Data', ['data' => $createResponse]);

            $resultCode = $createResponse['messages']['resultCode'] ?? 'Error';

            if ($resultCode === 'Ok') {
                $transactionResponse = $createResponse['transactionResponse'] ?? [];
                $responseCode = $transactionResponse['responseCode'] ?? null;

                if ($responseCode == '1') {
                    return [
                        'success'        => true,
                        'transaction_id' => $transactionResponse['transId'] ?? null,
                        'auth_code'      => $transactionResponse['authCode'] ?? null,
                        'message'        => 'Payment processed successfully',
                    ];
                } else {
                    $errors    = $transactionResponse['errors'] ?? [];
                    $errorCode = $errors[0]['errorCode'] ?? 'Unknown';
                    $errorText = $errors[0]['errorText'] ?? 'Transaction declined';

                    return [
                        'success'    => false,
                        'message'    => $this->getFriendlyErrorMessage((string)$errorCode, $errorText),
                        'error_code' => $errorCode,
                    ];
                }
            } else {
                $messages  = $createResponse['messages']['message'] ?? [];
                $errorCode = $messages[0]['code'] ?? 'E00001';
                $errorText = $messages[0]['text'] ?? 'Payment failed';

                return [
                    'success'    => false,
                    'message'    => $this->getFriendlyErrorMessage((string)$errorCode, $errorText),
                    'error_code' => $errorCode,
                ];
            }

        } catch (\Exception $e) {
            Log::error('Authorize.net exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Payment gateway error.'];
        }
    }

    /**
     * Get Country Full Name (for Authorize.net)
     */
    private function getCountryName($country)
    {
        return match (strtolower($country)) {
            'us', 'usa', 'united states' => 'United States',
            'pk', 'pakistan' => 'Pakistan',
            'gb', 'uk', 'united kingdom' => 'United Kingdom',
            'ca', 'canada' => 'Canada',
            'au', 'australia' => 'Australia',
            'ae', 'uae' => 'United Arab Emirates',
            'in', 'india' => 'India',
            'de', 'germany' => 'Germany',
            'fr', 'france' => 'France',
            default => 'United States',
        };
    }

    /**
     * Friendly Error Messages
     */
    private function getFriendlyErrorMessage($code, $default)
    {
        return match ($code) {
            '6'  => 'Invalid card number. Please check and try again.',
            '7'  => 'Invalid expiration date. Please check and try again.',
            '8'  => 'Invalid CVV code. Please check and try again.',
            '10' => 'Transaction declined. Please try a different card.',
            '11' => 'Duplicate transaction detected.',
            '27' => 'Address verification failed. Please check your billing address.',
            '37' => 'Card number is invalid.',
            '44' => 'Card code (CVV) is invalid.',
            '45' => 'Card code or zip code is invalid.',
            '65' => 'Transaction declined. Card has been declined.',
            '78' => 'Card type is not accepted.',
            '248' => 'Transaction timed out. Please try again.',
            '249' => 'Transaction limit exceeded.',
            '250' => 'Transaction rejected by fraud detection.',
            '251' => 'Transaction declined by bank.',
            default => $default,
        };
    }

    
}