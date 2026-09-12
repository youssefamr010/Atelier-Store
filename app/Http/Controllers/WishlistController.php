<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Toggle a product in customer's wishlist.
     */
    public function toggle(Request $request): JsonResponse
    {
        $productId = (int) $request->input('product_id');
        $product = Product::findOrFail($productId);

        if (Auth::check()) {
            $user = Auth::user();
            $existing = Wishlist::where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existing) {
                $existing->delete();
                $isWishlisted = false;
                $message = "Removed {$product->title} from your wishlist.";
            } else {
                Wishlist::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ]);
                $isWishlisted = true;
                $message = "Added {$product->title} to your wishlist.";
            }

            $count = Wishlist::where('user_id', $user->id)->count();

            return response()->json([
                'success' => true,
                'is_wishlisted' => $isWishlisted,
                'count' => $count,
                'message' => $message,
                'authenticated' => true,
            ]);
        }

        // Guest response
        return response()->json([
            'success' => true,
            'is_wishlisted' => (bool) $request->input('state', true),
            'message' => "Saved to your local wishlist. Log in to sync across devices.",
            'authenticated' => false,
            'prompt_login' => true,
        ]);
    }

    /**
     * Get user's wishlist IDs (for instant UI state hydration).
     */
    public function getIds(): JsonResponse
    {
        if (Auth::check()) {
            $ids = Wishlist::where('user_id', Auth::id())->pluck('product_id')->toArray();
            return response()->json(['ids' => $ids]);
        }

        return response()->json(['ids' => []]);
    }

    /**
     * Sync local guest wishlist on login.
     */
    public function syncLocal(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['success' => false], 401);
        }

        $user = Auth::user();
        $ids = (array) $request->input('product_ids', []);

        foreach ($ids as $id) {
            $productId = (int) $id;
            if ($productId > 0 && Product::where('id', $productId)->exists()) {
                Wishlist::firstOrCreate([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                ]);
            }
        }

        $allIds = Wishlist::where('user_id', $user->id)->pluck('product_id')->toArray();

        return response()->json([
            'success' => true,
            'ids' => $allIds,
            'count' => count($allIds),
        ]);
    }

    /**
     * Delete an item from wishlist.
     */
    public function destroy(int $id)
    {
        if (Auth::check()) {
            Wishlist::where('user_id', Auth::id())
                ->where(function ($q) use ($id) {
                    $q->where('id', $id)->orWhere('product_id', $id);
                })
                ->delete();
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Item removed from your wishlist.');
    }
}
