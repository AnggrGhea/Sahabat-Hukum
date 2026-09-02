<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isAdvokat()
    {
        return $this->role === 'advokat';
    }

    public function isKlien()
    {
        return $this->role === 'klien';
    }

    public function isActive()
    {
        return $this->status === 'aktif';
    }

    // Relationships
    public function clientProfile()
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function lawyerProfile()
    {
        return $this->hasOne(LawyerProfile::class);
    }

    public function consultationsAsClient()
    {
        return $this->hasMany(Consultation::class, 'client_id');
    }

    public function consultationsAsLawyer()
    {
        return $this->hasMany(Consultation::class, 'lawyer_id');
    }

    public function casesAsClient()
    {
        return $this->hasMany(LegalCase::class, 'client_id');
    }

    public function casesAsLawyer()
    {
        return $this->hasMany(LegalCase::class, 'lawyer_id');
    }

    public function schedulesAsLawyer()
    {
        return $this->hasMany(Schedule::class, 'lawyer_id');
    }

    public function schedulesAsClient()
    {
        return $this->hasMany(Schedule::class, 'client_id');
    }
}
