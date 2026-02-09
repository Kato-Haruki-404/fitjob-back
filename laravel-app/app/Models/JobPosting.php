<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    protected $fillable = [
        'title',
        'company_name',
        'email',
        'tel',
        'salary_type',
        'wage',
        'employment_type',
        'external_link_url',
        'status',
        'image',
        'address_id',
    ];

    protected $hidden = [
        'email',
        'tel',
        'address_id',
    ];

    /**
     * Get the image URL with full path.
     */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? \Illuminate\Support\Facades\Storage::url($value) : null,
        );
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'job_posting_tag');
    }

    public function momentum()
    {
        return $this->hasOne(Momentum::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
