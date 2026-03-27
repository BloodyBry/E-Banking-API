<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'birth_date',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function accounts()
    {
        return $this->belongsToMany(Account::class)
            ->withPivot('is_primary_holder', 'accepted_closure')
            ->withTimestamps();
    }

    public function initiatedTransfers()
    {
        return $this->hasMany(Transfer::class, 'initiated_by_user_id');
    }

    public function createdTransactions()
    {
        return $this->hasMany(Transaction::class, 'created_by_user_id');
    }
}