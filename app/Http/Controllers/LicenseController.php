<?php

namespace App\Http\Controllers;

use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LicenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // get licenses by subscribe or all
        $query = License::query();

        if ($request->filled('subscription_id')) {
            $query->where('subscription_id', $request->subscription_id);
        }

        $licenses = $query->get();
        return response()->json($licenses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscription_id' => 'required|exists:subscriptions,id',
            'device_id' => 'required|string|unique:licenses,device_id',
            'device_model' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $license = License::create($request->all());

        return response()->json([
            'message' => 'License created successfully',
            'data' => $license
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $license = License::with('subscription')->find($id);

        if (!$license) {
            return response()->json(['message' => 'License not found'], 404);
        }

        return response()->json($license);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $license = License::find($id);

        if (!$license) {
            return response()->json(['message' => 'License not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'device_model' => 'nullable|string',
            'last_sync_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $license->update($request->all());

        return response()->json([
            'message' => 'License updated successfully',
            'data' => $license
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $license = License::find($id);

        if (!$license) {
            return response()->json(['message' => 'License not found'], 404);
        }

        $license->delete();

        return response()->json(['message' => 'License deleted successfully']);
    }
}
