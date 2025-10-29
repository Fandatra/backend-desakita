<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HeadOfFamily extends Model
{
    use HasFactory;

    protected $table = 'head_of_families';

    protected $fillable = [
        'user_id',
        'profile_picture',
        'nik',
        'gender',
        'date_of_birth',
        'phone_number',
        'address',
        'occupation',
        'marital_status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class, 'head_of_family_id');
    }

    public function aidApplications(): HasMany
    {
        return $this->hasMany(AidApplication::class);
    }
}
