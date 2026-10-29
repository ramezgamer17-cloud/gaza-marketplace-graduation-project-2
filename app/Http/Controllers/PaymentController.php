<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function show(Order $order): View
    {
        $this->authorizeOwner($order);

        return view('payment.show', compact('order'));
    }

    public function sendCode(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOwner($order);

        $validated = $request->validate([
            'payment_phone' => ['required', 'regex:/^05\d{8}$/'],
        ], [
            'payment_phone.regex' => 'رقم محفظة جوال بي يجب أن يكون 10 أرقام ويبدأ بـ 05.',
        ], [
            'payment_phone' => 'رقم المحفظة',
        ]);

        // رمز تجريبي ثابت مرتبط بالطلب (وليس عشوائيًا)
        $code = str_pad((string) (1000 + (($order->id * 37) % 9000)), 4, '0', STR_PAD_LEFT);

        $order->update([
            'payment_phone' => $validated['payment_phone'],
            'payment_code' => $code,
            'payment_method' => 'jawwal_pay',
        ]);

        return redirect()
            ->route('payment.verify', $order)
            ->with('test_code', $code);
    }

    public function verifyForm(Order $order): View
    {
        $this->authorizeOwner($order);

        return view('payment.verify', compact('order'));
    }

    public function verify(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOwner($order);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10'],
        ], [], ['code' => 'رمز التحقق']);

        if (! $order->payment_code || $validated['code'] !== $order->payment_code) {
            return back()->withErrors(['code' => 'رمز التحقق غير صحيح.']);
        }

        $order->update([
            'payment_status' => 'paid',
            'status' => Order::STATUS_CONFIRMED,
            'payment_reference' => 'JPAY-'.$order->order_number,
        ]);

        // إن كانت هناك فواتير أخرى من نفس الطلب، انتقل لدفعها
        $pendingIds = collect($request->session()->get('checkout_orders', []))
            ->reject(fn ($id) => (int) $id === $order->id)
            ->values();

        $next = Order::query()
            ->whereIn('id', $pendingIds)
            ->where('payment_method', 'jawwal_pay')
            ->where('payment_status', '!=', 'paid')
            ->orderBy('id')
            ->first();

        if ($next) {
            $pending = $pendingIds->reject(fn ($id) => (int) $id === $next->id)->values();
            $request->session()->put('checkout_orders', $pending->all());

            return redirect()
                ->route('payment.show', $next)
                ->with('status', 'تم دفع الفاتورة الأولى. أكمل دفع الفاتورة التالية.');
        }

        $request->session()->forget('checkout_orders');

        return redirect()
            ->route('orders.index')
            ->with('status', 'تم الدفع بنجاح — طلبك مؤكد وقيد التنفيذ.');
    }

    protected function authorizeOwner(Order $order): void
    {
        abort_unless($order->user_id === auth()->id(), 403);
    }
}
