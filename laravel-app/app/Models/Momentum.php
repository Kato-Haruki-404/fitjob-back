<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Momentum extends Model
{
    protected $fillable = [
        'job_posting_id',
        'calorie',
        'steps',
        'exercise_level',
    ];

    protected $hidden = [
        'id',
        'job_posting_id',
    ];

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }
}
