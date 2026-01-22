<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceCommand extends Model
{
    protected $fillable = [
        'device_id',
        'machine_id',
        'is_active',
        'last_seen_at',
        'wipe_command',
        'wipe_requested_at',
        'wipe_completed_at',
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
