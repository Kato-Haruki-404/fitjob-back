<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
    ];

    public function recruitments()
    {
        return $this->belongsToMany(Recruitment::class, 'recruitment_tags');
    }
}
