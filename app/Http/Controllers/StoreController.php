<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\VendorProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request): View
    {
        $stores = VendorProfile::query()
            ->active()
            ->with(['user' => fn ($query) => $query->withCount('products')])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';

                $query->where(function ($query) use ($term) {
                    $query->where('store_name', 'like', $term)
                        ->orWhere('store_type', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->orderByDesc('is_featured')
            ->orderBy('store_name')
            ->paginate(12)
            ->withQueryString();

        return view('stores.index', [
            'stores' => $stores,
            'query' => $request->string('q')->toString(),
        ]);
    }

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

        $isFavorited = $vendorProfile->isFavoritedBy(auth()->id());
        $ratingAverage = $vendorProfile->ratingAverage();
        $ratingCount = $vendorProfile->ratingCount();
        $userRating = auth()->check()
            ? $vendorProfile->ratings()->where('user_id', auth()->id())->value('rating')
            : null;

        return view('stores.show', compact(
            'vendorProfile', 'sections', 'products', 'uncategorized',
            'isFavorited', 'ratingAverage', 'ratingCount', 'userRating'
        ));
    }
}
