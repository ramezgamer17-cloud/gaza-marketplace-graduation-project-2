<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->visibleInMarketplace()
            ->whereHas('favoritedBy', fn ($query) => $query->where('users.id', auth()->id()))
            ->with(['vendor.vendorProfile', 'section'])
            ->latest()
            ->paginate(12);

        $stores = \App\Models\VendorProfile::query()
            ->active()
            ->whereHas('favorites', fn ($query) => $query->where('user_id', auth()->id()))
            ->with(['user' => fn ($query) => $query->withCount('products')])
            ->get();

        return view('favorites.index', compact('products', 'stores'));
    }

    public function toggle(Request $request, Product $product): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = auth()->user();

        $existing = $user->favorites()->where('product_id', $product->id)->first();
        $favorited = $existing === null;

        if ($existing) {
            $existing->delete();
            $message = 'أُزيل «'.$product->name.'» من المفضلة.';
        } else {
            $user->favorites()->create(['product_id' => $product->id]);
            $message = 'أُضيف «'.$product->name.'» إلى المفضلة.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'favorited' => $favorited,
                'count' => $user->favorites()->count(),
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
    }
}
