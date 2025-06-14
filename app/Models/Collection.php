<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    /** @use HasFactory<\Database\Factories\CollectionFactory> */
    use HasFactory;
    protected $fillable = [
        'receipt_id',
        'client_id',
        'date',
        'amount',
        'notes',
        'machine_id',
        'client_name',
    ];

    //client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    //machine
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    //collection items
    public function collectionItems()
    {
        return $this->hasMany(CollectionItem::class);
    }

    //company 
}
