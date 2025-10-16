<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // 'admin' atau 'user'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relasi: 1 user -> 1 head of family (opsional)
    public function headOfFamily(): HasOne
    {
        return $this->hasOne(HeadOfFamily::class);
    }

    // Relasi: 1 user -> banyak aid applications (jika user adalah kepala keluarga)
    public function aidApplications()
    {
        return $this->hasMany(AidApplication::class);
    }
}
