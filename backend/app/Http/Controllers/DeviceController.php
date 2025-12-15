<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeviceController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'socket_id' => 'required|string',
            'device_info' => 'nullable|array',
        ]);

        $device = Device::updateOrCreate(
            ['socket_id' => $validated['socket_id']],
            ['device_info' => $validated['device_info'] ?? null]
        );

        return response()->json([
            'message' => 'Device registered successfully',
            'device' => $device,
        ], 201);
    }
}
