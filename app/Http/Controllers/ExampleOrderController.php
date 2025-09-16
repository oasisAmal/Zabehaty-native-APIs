<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Users\App\Models\User;

class ExampleOrderController extends Controller
{
    /**
     * Add item to cart (allowed for guests)
     */
    public function addToCart(Request $request)
    {
        // This endpoint allows both guests and registered users
        $user = $request->user();
        
        if (!$user) {
            // Create a guest user if no user is authenticated
            $user = User::createGuest([
                'name' => 'Guest User',
            ]);
        }

        // Add item to cart logic here
        // For example:
        // Cart::add($user->id, $request->product_id, $request->quantity);

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'data' => [
                'user_id' => $user->id,
                'is_guest' => $user->isGuest(),
            ]
        ]);
    }

    /**
     * Create order (only for registered users)
     */
    public function createOrder(Request $request)
    {
        // This endpoint requires registered users only
        // The middleware will handle the guest check
        
        $user = $request->user();
        
        // Create order logic here
        // For example:
        // $order = Order::create([
        //     'user_id' => $user->id,
        //     'items' => $request->items,
        //     'total' => $request->total,
        // ]);

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => [
                'user_id' => $user->id,
                'is_guest' => $user->isGuest(),
            ]
        ]);
    }

    /**
     * Get user cart (allowed for guests)
     */
    public function getCart(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No user found',
            ], 401);
        }

        // Get cart items logic here
        // For example:
        // $cartItems = Cart::where('user_id', $user->id)->get();

        return response()->json([
            'success' => true,
            'message' => 'Cart retrieved successfully',
            'data' => [
                'user_id' => $user->id,
                'is_guest' => $user->isGuest(),
                'items' => [], // Cart items would go here
            ]
        ]);
    }
}
