<?php

namespace App\Http\Controllers;

use App\Models\DeliveryZone;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\DeliveryFeeCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
        private readonly DeliveryFeeCalculator $fees,
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $cart = $this->cart->forUser(auth()->user())->fresh();
        $groups = $cart->vendorGroups();

        if ($groups->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'سلتك فارغة، أضف منتجات أولًا.');
        }

        $regions = DeliveryZone::query()->regions()->get();
        $selectedZoneId = $request->integer('zone_id') ?: null;

        $fees = $selectedZoneId ? $this->fees->feesForGroups($groups, $selectedZoneId) : [];

        $rows = $groups->map(function ($group) use ($fees, $selectedZoneId) {
            $fee = $selectedZoneId ? ($fees[$group->vendor->id] ?? null) : null;

            return (object) [
                'vendor' => $group->vendor,
                'items' => $group->items,
                'subtotal' => $group->subtotal,
                'fee' => $fee,
                'total' => $fee !== null ? $group->subtotal + $fee : null,
                'has_zone' => (bool) $group->vendor->vendorProfile?->zone_id,
            ];
        });

        $deliveryTotal = $this->fees->total($fees);
        $subtotal = (float) $groups->sum(fn ($group) => $group->subtotal);

        return view('checkout.index', [
            'groups' => $groups,
            'rows' => $rows,
            'regions' => $regions,
            'selectedZoneId' => $selectedZoneId,
            'subtotal' => $subtotal,
            'deliveryTotal' => $deliveryTotal,
            'grandTotal' => $subtotal + $deliveryTotal,
            'user' => auth()->user(),
        ]);
    }

    /**
     * أسعار التوصيل لمنطقة معيّنة (تُستخدم للتحديث الفوري في الصفحة).
     */
    public function fees(Request $request): JsonResponse
    {
        $request->validate([
            'zone_id' => ['required', 'exists:delivery_zones,id'],
        ]);

        $cart = $this->cart->forUser(auth()->user())->fresh();
        $groups = $cart->vendorGroups();
        $fees = $this->fees->feesForGroups($groups, (int) $request->input('zone_id'));

        $rows = $groups->map(function ($group) use ($fees) {
            $fee = $fees[$group->vendor->id] ?? null;

            return [
                'vendor_id' => $group->vendor->id,
                'fee' => $fee,
                'total' => $fee !== null ? round($group->subtotal + $fee, 2) : null,
            ];
        })->values();

        $deliveryTotal = $this->fees->total($fees);
        $subtotal = (float) $groups->sum(fn ($group) => $group->subtotal);

        return response()->json([
            'rows' => $rows,
            'delivery_total' => round($deliveryTotal, 2),
            'grand_total' => round($subtotal + $deliveryTotal, 2),
            'has_missing' => collect($fees)->contains(fn ($fee) => $fee === null),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'regex:/^05\d{8}$/'],
            'delivery_zone_id' => ['required', 'exists:delivery_zones,id'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:jawwal_pay,cash_on_delivery'],
        ], [
            'customer_phone.regex' => 'رقم الجوال يجب أن يكون 10 أرقام ويبدأ بـ 05 (مثال: 0597784541).',
        ], [
            'customer_name' => 'الاسم',
            'customer_phone' => 'رقم الجوال',
            'delivery_zone_id' => 'منطقة التوصيل',
            'shipping_address' => 'تفاصيل العنوان',
            'notes' => 'الملاحظات',
            'payment_method' => 'طريقة الدفع',
        ]);

        $cart = $this->cart->forUser(auth()->user())->fresh();

        try {
            $orders = $this->checkout->checkout($cart, $validated);
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        // حفظ أرقام الفواتير لمتابعة الدفع واحدة تلو الأخرى
        $request->session()->put('checkout_orders', $orders->pluck('id')->all());

        $firstUnpaid = $orders->first(fn ($order) => $order->payment_method === 'jawwal_pay' && $order->payment_status !== 'paid');

        if ($firstUnpaid) {
            return redirect()
                ->route('payment.show', $firstUnpaid)
                ->with('status', 'تم إنشاء '.$orders->count().' فاتورة. أكمل الدفع الآن.');
        }

        return redirect()
            ->route('orders.index')
            ->with('status', 'تم إنشاء '.$orders->count().' فاتورة بنجاح. تابع طلباتك من هنا.');
    }
}
