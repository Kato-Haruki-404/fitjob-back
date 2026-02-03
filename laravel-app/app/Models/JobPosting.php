<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    protected $fillable = [
        'title',
        'company_name',
        'email',
        'tel',
        'salary',
        'wage',
        'access',
        'external_link_url',
        'is_published',
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'job_posting_tag');
    }
}
