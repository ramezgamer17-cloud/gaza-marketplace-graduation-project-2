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

    public function add(Request $request, Product $product): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        try {
            $this->cart->add(auth()->user(), $product, (int) $request->input('quantity', 1));
        } catch (RuntimeException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $exception->getMessage()], 422);
            }

            return back()->with('error', $exception->getMessage());
        }

        if ($request->expectsJson()) {
            $count = auth()->user()->cart?->items()->sum('quantity') ?? 0;

            return response()->json([
                'ok' => true,
                'count' => (int) $count,
                'message' => "أُضيف «{$product->name}» إلى السلة.",
            ]);
        }

        return back()->with('status', "أُضيف «{$product->name}» إلى السلة.");
    }

    public function update(Request $request, CartItem $item): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        abort_unless($item->cart->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        try {
            $this->cart->updateQuantity(auth()->user(), $item->id, (int) $validated['quantity']);
        } catch (RuntimeException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $exception->getMessage()], 422);
            }

            return back()->with('error', $exception->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json($this->cartState($item->fresh(), $item->product?->user_id));
        }

        return back()->with('status', 'تم تحديث السلة.');
    }

    public function remove(Request $request, CartItem $item): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        abort_unless($item->cart->user_id === auth()->id(), 403);

        $vendorId = $item->product?->user_id;
        $itemId = $item->id;
        $item->delete();

        if ($request->expectsJson()) {
            return response()->json(
                array_merge($this->cartState(null, $vendorId), ['ok' => true, 'removed_id' => $itemId])
            );
        }

        return back()->with('status', 'تم حذف المنتج من السلة.');
    }

    public function clear(): RedirectResponse
    {
        $this->cart->clear(auth()->user());

        return back()->with('status', 'تم تفريغ السلة.');
    }

    /**
     * حالة السلة بعد أي تعديل (تُستخدم لتحديث الواجهة بدون إعادة تحميل).
     *
     * @return array<string, mixed>
     */
    protected function cartState(?CartItem $item, ?int $vendorId): array
    {
        $cart = $this->cart->forUser(auth()->user())->fresh();
        $groups = $cart->vendorGroups();

        $group = $vendorId
            ? $groups->first(fn ($group) => $group->vendor->id === $vendorId)
            : null;

        return [
            'ok' => true,
            'item' => $item ? [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'line_total' => round((float) $item->line_total, 2),
            ] : null,
            'store' => [
                'vendor_id' => $vendorId,
                'subtotal' => $group ? round((float) $group->subtotal, 2) : 0,
                'items' => $group ? $group->items->count() : 0,
                'empty' => $group === null,
            ],
            'total' => round((float) $cart->grand_total, 2),
            'count' => (int) $cart->items()->sum('quantity'),
        ];
    }
}
