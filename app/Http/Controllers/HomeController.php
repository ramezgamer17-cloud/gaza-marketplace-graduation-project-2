<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\VendorProfile;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->roots()
            ->withCount('products')
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        $stores = VendorProfile::query()
            ->active()
            ->with(['user' => fn ($query) => $query->withCount('products')])
            ->orderByDesc('is_featured')
            ->take(6)
            ->get();

        $featuredProducts = Product::query()
            ->visibleInMarketplace()
            ->inStock()
            ->where('is_featured', true)
            ->with(['vendor.vendorProfile', 'section'])
            ->latest()
            ->take(8)
            ->get();

        $newProducts = Product::query()
            ->visibleInMarketplace()
            ->inStock()
            ->with(['vendor.vendorProfile', 'section'])
            ->latest()
            ->take(8)
            ->get();

        return view('home', compact('categories', 'stores', 'featuredProducts', 'newProducts'));
    }
}
