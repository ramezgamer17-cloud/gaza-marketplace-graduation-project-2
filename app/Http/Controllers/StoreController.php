<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\VendorProfile;
use Illuminate\Contracts\View\View;

class StoreController extends Controller
{
    public function show(VendorProfile $vendorProfile): View
    {
        abort_unless($vendorProfile->is_active, 404);

        $vendorProfile->load('user');

        $sections = $vendorProfile->user
            ->sections()
            ->where('is_active', true)
            ->with(['products' => fn ($query) => $query->active()->inStock()->orderByDesc('is_featured')])
            ->orderBy('sort_order')
            ->get();

        $products = Product::query()
            ->active()
            ->inStock()
            ->where('user_id', $vendorProfile->user_id)
            ->with('section')
            ->latest()
            ->get();

        $uncategorized = $products->whereNull('section_id');

        return view('stores.show', compact('vendorProfile', 'sections', 'products', 'uncategorized'));
    }
}
