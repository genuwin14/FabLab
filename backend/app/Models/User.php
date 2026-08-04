<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, \Laravel\Sanctum\HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fullname',
        'email',
        'password',
        'role',
        'address',
        'contact_number',
        // Google sign-up mass-assigns this; without it here the value was
        // silently dropped and those accounts never read as email-verified.
        'email_verified_at',
        'phone_verified',
        'phone_verification_code',
        'degree',
        'year',
        'section',
        'gender',
        'photo',
        'status',
        'notifications_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notifications_enabled' => 'boolean',
        ];
    }

    /**
     * The named route this user lands on after signing in, and the one they're
     * sent back to if they wander into an area their role can't reach.
     */
    public function homeRoute(): string
    {
        return match ($this->role) {
            'admin' => 'admin.dashboard',
            'staff' => 'staff.dashboard',
            default => 'customer.shop',
        };
    }

    /**
     * Get the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the customized designs created by the user.
     */
    public function customDesigns()
    {
        return $this->hasMany(CustomDesign::class);
    }
}
