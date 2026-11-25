<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembagiPoint extends Model
{
    protected $table = 'pembagi_points';

    protected $fillable = [
        'nominal',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
