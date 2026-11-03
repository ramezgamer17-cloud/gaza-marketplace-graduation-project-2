<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->where('user_id', auth()->id())
            ->with(['vendor.vendorProfile', 'items'])
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $order->load(['items', 'vendor.vendorProfile', 'deliveryUser']);

        return view('orders.invoice', compact('order'));
    }

    public function tracking(Order $order): View
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $order->load(['items', 'vendor.vendorProfile', 'deliveryUser', 'deliveryCompany', 'deliveryZone']);

        return view('orders.tracking', compact('order'));
    }

    /**
     * حالة الطلب الحالية (تُستخدم للتحديث المباشر بدون إعادة تحميل).
     */
    public function status(Order $order): \Illuminate\Http\JsonResponse
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $order->load(['deliveryUser', 'deliveryCompany']);

        $keys = ['pending', 'confirmed', 'preparing', 'ready', 'shipped', 'delivered'];
        $index = array_search($order->status, $keys, true);

        return response()->json([
            'status' => $order->status,
            'label' => Order::statusLabels()[$order->status] ?? $order->status,
            'index' => $index === false ? 0 : $index,
            'cancelled' => $order->status === Order::STATUS_CANCELLED,
            'updated_at' => $order->updated_at?->format('Y-m-d H:i'),
            'driver' => $order->deliveryUser?->name,
            'driver_phone' => $order->deliveryUser?->phone,
            'company' => $order->deliveryCompany?->name,
        ]);
    }
}
