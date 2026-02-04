<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'pivot',
        'created_at',
        'updated_at',
        'jobPostings',
    ];

    public function jobPostings()
    {
        return $this->belongsToMany(JobPosting::class, 'job_posting_tag');
    }
}
