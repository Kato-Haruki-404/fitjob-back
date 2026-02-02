<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'recruitment_id',
        'account_id',
        'age',
        'gender',
        'score',
        'comment',
    ];

    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'account_id');
    }
}
