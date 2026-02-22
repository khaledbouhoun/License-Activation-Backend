<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subscriptions = Subscription::with(['client', 'application'])
            ->withCount('licenses')
            ->get();
        return response()->json($subscriptions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'application_id' => 'required|exists:applications,id',
            'max_devices' => 'required|integer|min:1',
            'duration' => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Generate a random license key if not provided
        $data = $request->all();
        if (empty($data['license_key'])) {
            $data['license_key'] = strtoupper(
                Str::upper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4))
            );
        }

        $subscription = Subscription::create($data);

        return response()->json([
            'message' => 'Subscription created successfully',
            'data' => $subscription
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $subscription = Subscription::with(['client', 'application', 'licenses'])->find($id);

        if (!$subscription) {
            return response()->json(['message' => 'Subscription not found'], 404);
        }

        return response()->json($subscription);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $subscription = Subscription::find($id);

        if (!$subscription) {
            return response()->json(['message' => 'Subscription not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'client_id' => 'sometimes|required|exists:clients,id',
            'application_id' => 'sometimes|required|exists:applications,id',
            'max_devices' => 'sometimes|required|integer|min:1',
            'duration' => 'sometimes|required|integer|min:1',
            'start_date' => 'sometimes|nullable|date',
            'expiry_date' => 'sometimes|nullable|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $subscription->update($request->all());

        return response()->json([
            'message' => 'Subscription updated successfully',
            'data' => $subscription
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $subscription = Subscription::find($id);

        if (!$subscription) {
            return response()->json(['message' => 'Subscription not found'], 404);
        }

        $subscription->delete();

        return response()->json(['message' => 'Subscription deleted successfully']);
    }
}
