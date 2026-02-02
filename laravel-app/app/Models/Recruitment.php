<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recruitment extends Model
{
    protected $fillable = [
        'client_id',
        'address_id',
        'thumbnail_id',
        'title',
        'work_content',
        'atmosphere_description',
        'welfare_description',
        'appeal_points',
        'employment_type',
        'ideal_candidate',
        'precautions',
        'break_time_minutes',
        'hourly_wage',
        'daily_wage',
        'calories_burned',
        'motion_level',
        'capacity',
        'is_template',
        'is_published',
    ];

    protected $casts = [
        'is_template' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function thumbnail()
    {
        return $this->belongsTo(FilePath::class, 'thumbnail_id');
    }

    public function images()
    {
        return $this->belongsToMany(FilePath::class, 'recruitment_images', 'recruitment_id', 'image_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'recruitment_tags');
    }

    public function workers()
    {
        return $this->belongsToMany(User::class, 'workers', 'recruitment_id', 'account_id')
            ->withTimestamps();
    }

    public function workShifts()
    {
        return $this->hasMany(WorkShift::class);
    }

    public function publishPeriods()
    {
        return $this->hasMany(PublishPeriod::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
