<?php
namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\CheckoutItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CheckoutController extends Controller
{
    // STEP 1: Save Address & Create Draft Checkout
    public function saveAddress(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'country' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'required|string',
        ]);

        $userId = auth()->id();

        // Check if user has a draft checkout
        $checkout = Checkout::where('user_id', $userId)
            ->where('status', 'draft')
            ->first();

        if ($checkout) {
            $checkout->update($validated);
        } else {
            $checkout = Checkout::create([
                'user_id' => $userId,
                ...$validated,
                'status' => 'draft',
            ]);
        }

        return response()->json([
            'success' => true,
            'checkout_id' => $checkout->id,
            'message' => 'Address saved.',
        ]);
    }

    // STEP 2: Update Shipping Method
    public function saveShipping(Request $request)
    {
        $validated = $request->validate([
            'checkout_id' => 'required|exists:checkouts,id',
            'shipping_method' => 'required|string',
            'shipping_price' => 'required|numeric',
        ]);

        $checkout = Checkout::where('id', $validated['checkout_id'])
            ->where('user_id', auth()->id())
            ->where('status', 'draft')
            ->firstOrFail();

        $checkout->update([
            'shipping_method' => $validated['shipping_method'],
            'shipping_price' => $validated['shipping_price'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shipping method selected.',
        ]);
    }


    public function getOrder($id)
    {
        $order = Checkout::with('items')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $order,
        ]);
    }





    // Admin: Get all orders (Paginated)
    public function adminIndex()
    {
        $orders = \App\Models\Checkout::with('user:id,name,email')
            ->where('status', '!=', 'draft') // Draft orders hide karein
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $orders,
        ]);
    }

    // Admin: Get single order details
    public function adminShow($id)
    {
        $order = \App\Models\Checkout::with(['user:id,name,email', 'items'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $order,
        ]);
    }



    
    public function userOrders()
    {
        $orders = \App\Models\Checkout::with(['items'])
            ->where('user_id', auth()->id())
            ->where('status', '!=', 'draft')
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $orders,
        ]);
    }

    // User Order Details (Naya Method)
    public function userOrderShow($id)
    {
        $order = \App\Models\Checkout::with(['items'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $order,
        ]);
    }


}
