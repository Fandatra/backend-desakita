<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
            'title',
            'pic',
            'description',
            'event_photo',
            'location',
            'date',
            'time',
    ];

    protected $casts = [
        'date' => 'date',
        // time biarkan string; kalau mau cast ke datetime, sesuaikan
    ];
}
