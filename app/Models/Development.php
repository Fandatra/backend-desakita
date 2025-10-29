<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Development extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'pic',
        'description',
        'location',
        'status', // planning, ongoing, completed
        'budget',
        'start_date',
        'end_date',
        'photo',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        // kalau mau casting budget sebagai decimal:
        'budget' => 'decimal:2',
    ];
}
