<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositMethod extends Model
{
    protected $fillable = [
        'name',
        'type',
        'is_active',
        'details',
        'instructions'
    ];

    protected $casts = [
        'details' => 'array',
        'is_active' => 'boolean'
    ];
}