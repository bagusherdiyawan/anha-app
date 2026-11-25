<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment_status extends Model
{
    protected $table = 'payment_statuss';
    protected $fillable = [
        'id',
        'nama',
        'keterangan',
    ];
}