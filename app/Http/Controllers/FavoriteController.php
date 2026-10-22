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

        return view('favorites.index', compact('products'));
    }

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        $user = auth()->user();

        $existing = $user->favorites()->where('product_id', $product->id)->first();

        if ($existing) {
            $existing->delete();
            $message = 'أُزيل «'.$product->name.'» من المفضلة.';
        } else {
            $user->favorites()->create(['product_id' => $product->id]);
            $message = 'أُضيف «'.$product->name.'» إلى المفضلة.';
        }

        return back()->with('status', $message);
    }
}
