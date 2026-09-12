<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StorefrontReviewController extends Controller
{
    /**
     * Submit or update a verified product review.
     */
    public function store(Request $request, int $productId)
    {
        if (!Auth::check()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please log in to your account to review this product.',
                ], 401);
            }
            return back()->with('error', 'Please log in to review this product.');
        }

        $user = Auth::user();
        $product = Product::findOrFail($productId);

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:150',
            'comment' => 'required|string|min:5|max:2000',
        ]);

        // Verified purchase check (must have a delivered order containing this product)
        $userEmail = strtolower((string)$user->email);
        $hasDeliveredOrder = Order::where(function ($q) use ($user, $userEmail) {
                $q->where('customer_email', $userEmail);
                if ($user->customer_id) {
                    $q->orWhere('customer_id', $user->customer_id);
                }
            })
            ->where('status', 'delivered')
            ->whereHas('items', function ($iq) use ($productId) {
                $iq->where('product_id', $productId);
            })
            ->exists();

        if (!$hasDeliveredOrder && !$user->isAdmin()) {
            $msg = 'Verified Purchase required: Only clients who have completed and received an order for this piece can submit a review.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        // Prevent multiple reviews: update existing or create new
        $review = Review::updateOrCreate(
            [
                'user_id' => $user->id,
                'product_id' => $product->id,
            ],
            [
                'rating' => (int) $request->input('rating'),
                'title' => $request->input('title') ?: ($request->input('rating') >= 4 ? 'Exceptional Craftsmanship' : 'Product Feedback'),
                'comment' => $request->input('comment'),
                'is_verified_purchase' => true,
                'is_approved' => false, // Requires admin moderation
            ]
        );

        $msg = 'Thank you for your review. It has been received and will be published following quality moderation.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }
}
