<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Machine model.
 *
 * Columns `is_active` (bool) and `status` (enum active/inactive/maintenance) are kept in sync
 * via the booted() observer:
 *   - Changing `status` updates `is_active = ($status === 'active')`.
 *   - Changing `is_active` updates `status` to 'active' or 'inactive' (never touches 'maintenance').
 * Old code paths that write `is_active` continue to work; admin paths use `status`.
 */
class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'serial_number',
        'is_active',
        'status',
        'type',
        'installation_date',
        'description',
        'collector_id',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Machine $machine) {
            if ($machine->isDirty('status')) {
                $machine->is_active = ($machine->status === 'active');
            } elseif ($machine->isDirty('is_active')) {
                // Only flip between active/inactive — don't overwrite 'maintenance'
                if ($machine->status !== 'maintenance') {
                    $machine->status = $machine->is_active ? 'active' : 'inactive';
                }
            }
        });
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function deviceCommand()
    {
        return $this->hasOne(DeviceCommand::class);
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    public function shouldWipeData(): bool
    {
        return $this->deviceCommand?->wipe_command ?? false;
    }

    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
