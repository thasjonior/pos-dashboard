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

    protected $casts = [
        'wipe_requested_at' => 'datetime',
        'wipe_completed_at' => 'datetime',
        'last_seen_at'      => 'datetime',
        'wipe_command'      => 'boolean',
        'is_active'         => 'boolean',
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
