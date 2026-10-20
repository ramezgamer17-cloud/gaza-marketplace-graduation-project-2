<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
    ) {
    }

    public function index(): View|RedirectResponse
    {
        $cart = $this->cart->forUser(auth()->user())->fresh();
        $groups = $cart->vendorGroups();

        if ($groups->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'سلتك فارغة، أضف منتجات أولًا.');
        }

        return view('checkout.index', [
            'groups' => $groups,
            'grandTotal' => (float) $groups->sum(fn ($group) => $group->total),
            'user' => auth()->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:jawwal_pay,cash_on_delivery'],
        ], [], [
            'customer_name' => 'الاسم',
            'customer_phone' => 'رقم الجوال',
            'shipping_address' => 'عنوان التوصيل',
            'notes' => 'الملاحظات',
            'payment_method' => 'طريقة الدفع',
        ]);

        $cart = $this->cart->forUser(auth()->user())->fresh();

        try {
            $orders = $this->checkout->checkout($cart, $validated);
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('orders.index')
            ->with('status', 'تم إنشاء '.$orders->count().' فاتورة بنجاح. تابع طلباتك من هنا.');
    }
}
