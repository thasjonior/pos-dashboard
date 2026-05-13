<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Record an admin action. Captures IP and UA from the current HTTP request.
     */
    public static function record(User $user, string $action, Model $subject, ?array $changes = null): self
    {
        return static::create([
            'user_id'        => $user->id,
            'action'         => $action,
            'auditable_type' => get_class($subject),
            'auditable_id'   => $subject->getKey(),
            'changes'        => $changes,
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
        ]);
    }
}
