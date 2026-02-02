<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'account_id',
        'address_id',
        'logo_id',
        'thumbnail_id',
        'name',
        'tel',
        'website_url',
        'description',
        'representative_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'account_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function logo()
    {
        return $this->belongsTo(FilePath::class, 'logo_id');
    }

    public function thumbnail()
    {
        return $this->belongsTo(FilePath::class, 'thumbnail_id');
    }
}
