<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilePath extends Model
{
    protected $fillable = [
        'path',
        'extension',
    ];
}
