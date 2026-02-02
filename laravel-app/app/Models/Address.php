<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'postal_code',
        'prefecture',
        'city',
        'town',
        'address_line',
        'building_name',
        'latitude',
        'longitude',
        'line_name',
        'nearest_station',
        'walking_minutes',
    ];
}
