<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\License;
use Carbon\Carbon;

class ActivationController extends Controller
{
    /**
     * Activate a device for the first time using a license key.
     */
    public function activate(Request $request)
    {
        // 1. Validation
        $validated = $request->validate([
            'license_key' => 'required|string',
            'device_id' => 'required|string',
            'device_model' => 'nullable|string',
        ]);

        try {
            return DB::transaction(function () use ($request, $validated) {

                // 2. Find subscription with Eager Loading to save queries
                $subscription = Subscription::where('license_key', $validated['license_key'])->first();

                if (!$subscription) {
                    return response()->json(['error' => 'Invalid license key'], Response::HTTP_UNAUTHORIZED); // 401
                }

                $deviceCount = $subscription->licenses()->count();

                // 3. Logic for the first activation
                if ($deviceCount === 0) {
                    $subscription->update([
                        'start_date' => now(),
                        'expiry_date' => now()->addMonths($subscription->duration),
                        'is_active' => true,
                    ]);
                }

                // 4. Check status and expiry before adding new device
                $expiryDate = $subscription->expiry_date ? Carbon::parse($subscription->expiry_date) : null;
                if (!$subscription->is_active || ($expiryDate && $expiryDate->isPast())) {
                    return response()->json(['error' => 'Subscription expired or inactive'], Response::HTTP_FORBIDDEN); // 403
                }

                // 5. Check if device is already registered
                $license = $subscription->licenses()->where('device_id', $validated['device_id'])->first();

                if (!$license) {
                    // Check limit ONLY if it's a new device
                    if ($deviceCount >= $subscription->max_devices) {
                        return response()->json(['error' => 'Device limit reached'], Response::HTTP_FORBIDDEN); // 403
                    }

                    $license = $subscription->licenses()->create([
                        'device_id' => $validated['device_id'],
                        'device_model' => $validated['device_model'],
                        'last_sync_date' => now(),
                        'start_date' => now(),
                        'expiry_date' => $expiryDate,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Device activated successfully',
                    'subscription' => $subscription,
                    'license' => $license,
                ], Response::HTTP_OK); // 200
            });

        } catch (\Exception $e) {
            // تسجيل الخطأ في الـ Logs لمراجعته لاحقاً
            log::error("Activation Error: " . $e->getMessage());

            return response()->json([
                'error' => 'A server error occurred. Please try again later.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR); // 500
        }
    }

    /**
     * Daily check: ensure license is still valid.
     * Flutter app calls this once per day.
     */
    public function check(Request $request)
    {
        $validated = $request->validate([
            'license_key' => 'required|string',
            'device_id' => 'required|string',
        ]);

        // استخدام eager loading للرخص (licenses) يحسن الأداء
        $subscription = Subscription::with([
            'licenses' => function ($query) use ($validated) {
                $query->where('device_id', $validated['device_id']);
            }
        ])->where('license_key', $validated['license_key'])->first();

        // 401: الرخصة غير موجودة أصلاً
        if (!$subscription) {
            return response()->json(['error' => 'Invalid license key'], Response::HTTP_UNAUTHORIZED);
        }

        // 403: استدعاء دالة من الموديل للتحقق من الصلاحية (تنظيم أفضل)
        if (!$subscription->is_active || ($subscription->expiry_date && Carbon::parse($subscription->expiry_date)->isPast())) {
            return response()->json(['error' => 'Subscription expired or inactive'], Response::HTTP_FORBIDDEN);
        }

        // التحقق مما إذا كان هذا الجهاز بالتحديد مسجلاً لهذه الرخصة
        $license = $subscription->licenses->first();
        if (!$license) {
            return response()->json(['error' => 'Device not registered for this license'], Response::HTTP_FORBIDDEN);
        }

        // تحديث تاريخ آخر مزامنة (Last Sync) بصمت دون تغيير الـ Timestamps إذا أردت
        $license->update(['last_sync_date' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'License is valid',
            'subscription' => $subscription,
            'license' => $license,
        ], Response::HTTP_OK);
    }
}
