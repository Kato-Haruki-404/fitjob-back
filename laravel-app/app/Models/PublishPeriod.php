<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublishPeriod extends Model
{
    protected $fillable = [
        'recruitment_id',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class);
    }
}
