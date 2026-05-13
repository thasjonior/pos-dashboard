<?php

namespace App\Http\Controllers;
use App\Models\DeviceCommand;

use Illuminate\Http\Request;

class DeviceCommandController extends Controller
{
  

    public function register(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'machine_id' => 'required|integer',
        ]);

        $device = DeviceCommand::updateOrCreate(
            ['device_id' => $validated['device_id']],
            [
                'machine_id' => $validated['machine_id'],
                'last_seen_at' => now(),
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device registered',
        ]);
    }

    public function status($deviceId)
    {
        $device = DeviceCommand::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json([
                'wipe' => false,
                'disabled' => false,
            ]);
        }

        return response()->json([
            'wipe' => $device->wipe_command ? true : false,
            'disabled' => !$device->is_active,
        ]);
    }

    public function wipeConfirmed(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
        ]);

        DeviceCommand::where('device_id', $validated['device_id'])
            ->whereNull('wipe_completed_at')
            ->update([
                'wipe_command'      => false,
                'wipe_completed_at' => now(),
                'is_active'         => false,
            ]);

        return response()->json(['success' => true]);
    }
}
