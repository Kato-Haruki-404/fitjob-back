<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'is_company',
        'is_admin',
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
        ];
    }
    /**
     * Get the user profile associated with the user.
     */
    public function userProfile()
    {
        return $this->hasOne(UserProfile::class, 'account_id');
    }

    /**
     * Get the company profile associated with the user.
     */
    public function companyProfile()
    {
        return $this->hasOne(CompanyProfile::class, 'account_id');
    }

    /**
     * Get the recruitments posted by the user (as a company/client).
     */
    public function recruitments()
    {
        return $this->hasMany(Recruitment::class, 'client_id');
    }

    /**
     * Get the recruitments the user has applied to (as a worker).
     */
    public function appliedRecruitments()
    {
        return $this->belongsToMany(Recruitment::class, 'workers', 'account_id', 'recruitment_id')
            ->withTimestamps();
    }

    /**
     * Get the reviews written by the user.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'account_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notifications::class, 'account_id');
    }
}
