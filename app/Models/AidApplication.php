<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AidApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'social_aid_id',
        'head_of_family_id',
        'bank_account',
        'requested_nominal',
        'reason',
        'status', // pending, approved, rejected
        'proof_photo',
    ];

    protected $casts = [
        'requested_nominal' => 'decimal:2',
    ];

    public function socialAid(): BelongsTo
    {
        return $this->belongsTo(SocialAid::class);
    }

    public function headOfFamily(): BelongsTo
    {
        return $this->belongsTo(HeadOfFamily::class);
    }
}
