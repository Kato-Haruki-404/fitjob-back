<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserProfile extends Model
{
    protected $fillable = [
        'account_id',
        'address_id',
        'identity_document_id',
        'tel',
        'last_name',
        'last_name_kana',
        'first_name',
        'first_name_kana',
        'birthday',
        'gender',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birthday' => 'date',
        ];
    }

    /**
     * Get the user's age based on birthday.
     */
    public function getAgeAttribute()
    {
        return Carbon::parse($this->birthday)->age;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'account_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function identityDocument()
    {
        return $this->belongsTo(FilePath::class, 'identity_document_id');
    }
}
