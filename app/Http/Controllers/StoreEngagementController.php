<?php

namespace App\Http\Controllers;

use App\Models\VendorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreEngagementController extends Controller
{
    /**
     * إضافة/إزالة متجر من المفضلة.
     */
    public function toggleFavorite(Request $request, VendorProfile $vendorProfile): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        $existing = $vendorProfile->favorites()->where('user_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
            $state = false;
        } else {
            $vendorProfile->favorites()->create(['user_id' => $user->id]);
            $state = true;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'favorited' => $state,
                'count' => $vendorProfile->favorites()->count(),
                'message' => $state ? 'أُضيف المتجر إلى المفضلة' : 'أُزيل المتجر من المفضلة',
            ]);
        }

        return back()->with('status', $state ? 'أُضيف المتجر إلى المفضلة.' : 'أُزيل المتجر من المفضلة.');
    }

    /**
     * تقييم المتجر من 1 إلى 5 نجوم (يُحدَّث تقييم المستخدم إن وُجد).
     */
    public function rate(Request $request, VendorProfile $vendorProfile): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ], [], ['rating' => 'التقييم']);

        $vendorProfile->ratings()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['rating' => (int) $validated['rating']]
        );

        $average = $vendorProfile->ratingAverage();
        $count = $vendorProfile->ratingCount();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'average' => $average,
                'count' => $count,
                'user_rating' => (int) $validated['rating'],
                'message' => 'تم حفظ تقييمك، شكرًا لك!',
            ]);
        }

        return back()->with('status', 'تم حفظ تقييمك، شكرًا لك!');
    }
}
