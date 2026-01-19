<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Verificar si el usuario es admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verificar si el usuario es cliente
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Relación: Un usuario tiene muchos pedidos
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Obtener el total gastado por el usuario
     */
    public function getTotalSpentAttribute()
    {
        return $this->orders()
            ->where('status', 'completed')
            ->sum('total');
    }

    /**
     * Obtener cantidad de pedidos del usuario
     */
    public function getTotalOrdersAttribute()
    {
        return $this->orders()->count();
    }
}
