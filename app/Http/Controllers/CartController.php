<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index(): View
    {
        $cart = $this->cart->forUser(auth()->user());

        return view('cart.index', [
            'groups' => $cart->fresh()->vendorGroups(),
            'cart' => $cart,
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        try {
            $this->cart->add(auth()->user(), $product, (int) $request->input('quantity', 1));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('status', "أُضيف «{$product->name}» إلى السلة.");
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        abort_unless($item->cart->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        try {
            $this->cart->updateQuantity(auth()->user(), $item->id, (int) $validated['quantity']);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('status', 'تم تحديث السلة.');
    }

    public function remove(CartItem $item): RedirectResponse
    {
        abort_unless($item->cart->user_id === auth()->id(), 403);

        $item->delete();

        return back()->with('status', 'تم حذف المنتج من السلة.');
    }

    public function clear(): RedirectResponse
    {
        $this->cart->clear(auth()->user());

        return back()->with('status', 'تم تفريغ السلة.');
    }
}
