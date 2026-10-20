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

        // كل متجر مميز + المنتجات التي اختارها بنفسه (المميزة أولًا)
        $storeSections = VendorProfile::query()
            ->active()
            ->with('user')
            ->orderByDesc('is_featured')
            ->orderBy('store_name')
            ->take(6)
            ->get()
            ->map(function (VendorProfile $store) {
                $base = Product::query()
                    ->visibleInMarketplace()
                    ->inStock()
                    ->where('user_id', $store->user_id);

                // منتجات اختارها البائع كمميزة، وإن لم يختر شيئًا نعرض أحدث منتجاته
                $products = (clone $base)
                    ->where('is_featured', true)
                    ->latest()
                    ->take(6)
                    ->get();

                if ($products->isEmpty()) {
                    $products = $base->latest()->take(4)->get();
                }

                $store->setRelation('showcaseProducts', $products);

                return $store;
            })
            ->filter(fn (VendorProfile $store) => $store->showcaseProducts->isNotEmpty())
            ->values();

        return view('home', compact('categories', 'stores', 'featuredProducts', 'storeSections'));
    }
}
