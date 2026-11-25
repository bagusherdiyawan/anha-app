<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Biaya_antar extends Model
{
    protected $fillable = [
        'biaya',
        'status_active',
    ];
    protected $casts = [
        'biaya' => 'integer',
    ];
}