<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    /** @use HasFactory<\Database\Factories\MachineFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'serial_number',
        'is_active',
        'installation_date',
        'description',
        'collector_id',
        'company_id',
    ];


    //collector
    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    //company
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    //device commands
    public function deviceCommand()
    {
        return $this->hasOne(DeviceCommand::class); 
    }

    //should wipe data
    public function shouldWipeData(): bool
    {
        return $this->deviceCommand?->wipe_command ?? false;    
    }
}
