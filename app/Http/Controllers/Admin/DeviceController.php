<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DeviceCommand;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $devices = DeviceCommand::with('machine.company')
            ->when($request->status === 'pending', fn ($q) => $q->where('wipe_command', true)->whereNull('wipe_completed_at'))
            ->when($request->status === 'executed', fn ($q) => $q->whereNotNull('wipe_completed_at'))
            ->when($request->status === 'none', fn ($q) => $q->where('wipe_command', false))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.devices.index', compact('devices'));
    }

    public function show(DeviceCommand $device)
    {
        $device->load('machine.company');

        return view('admin.devices.show', compact('device'));
    }

    public function wipe(Request $request, DeviceCommand $device)
    {
        $request->validate([
            'confirm_device_id' => ['required', 'string', function ($attr, $value, $fail) use ($device) {
                if ($value !== $device->device_id) {
                    $fail('The device ID you entered does not match.');
                }
            }],
        ]);

        $device->update([
            'wipe_command'       => true,
            'wipe_requested_at'  => now(),
            'wipe_completed_at'  => null,
        ]);

        AuditLog::record(auth()->user(), 'wiped', $device->machine ?? $device, [
            'device_id' => $device->device_id,
        ]);

        return back()->with('success', 'Wipe queued. Will execute on the device\'s next status poll.');
    }

    public function markExecuted(DeviceCommand $device)
    {
        $device->update([
            'wipe_completed_at' => now(),
            'wipe_command'      => false,
        ]);

        AuditLog::record(auth()->user(), 'wipe-marked-executed', $device->machine ?? $device, [
            'device_id' => $device->device_id,
            'note'      => 'manually marked executed by ' . auth()->user()->name,
        ]);

        return back()->with('success', 'Wipe marked as executed.');
    }
}
