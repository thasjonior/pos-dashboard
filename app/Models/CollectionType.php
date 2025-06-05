<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionType extends Model
{
    /** @use HasFactory<\Database\Factories\CollectionTypeFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
    ];
}

