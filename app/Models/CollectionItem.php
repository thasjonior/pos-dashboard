<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionItem extends Model
{
    /** @use HasFactory<\Database\Factories\CollectionItemFactory> */
    use HasFactory;
    protected $fillable = [
        'collection_id',
        'collection_type_id',
        'amount',
    ];

    //collection
    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    //collection type
    public function collectionType()
    {
        return $this->belongsTo(CollectionType::class);
    }

}
