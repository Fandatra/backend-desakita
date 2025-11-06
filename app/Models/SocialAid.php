<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialAid extends Model
{
    use HasFactory;

    protected $table = 'social_aids';

    protected $fillable = [
        'category',      // contoh: 'bahan pokok', 'uang tunai', ...
        'aid_name',
        'thumbnail',
        'nominal',
        'donor_name',
        'description',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function recipients()
    {
        return $this->belongsToMany(HeadOfFamily::class, 'social_aid_recipients')
                    ->withPivot(['status', 'received_nominal', 'notes'])
                    ->withTimestamps();
    }

}
